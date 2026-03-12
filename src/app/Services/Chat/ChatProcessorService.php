<?php

namespace App\Services\Chat;

use App\Models\Neural;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\UserActivity;
use App\Services\Chat\ChatTitleService;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatProcessorService
{
    private ?Chat $chat = null;
    private bool $isNewChat = false;
    private Neural $neural;
    private string $fullResponse = '';

    public function __construct(
        private readonly ChatTitleService $titleService,
        private readonly ChatRepository $chatRepository,
        private readonly MessageRepository $messageRepository
    ) {}

    public function process(string $chatId, string $message, string $modelName, int $userId): Response
    {
        $this->neural = $this->getNeuralModel($modelName);
        $this->initializeChat($chatId, $message, $userId);

        $this->saveUserMessage($message);

        return $this->streamResponse($userId);
    }

    private function initializeChat(?string $chatId, string $message, int $userId): void
    {
        if (!$chatId || $chatId === 'new-chat') {
            $this->createNewChat($message, $userId);
            return;
        }

        $this->chat = $this->chatRepository->find($chatId);

        if (!$this->chat) {
            $this->createNewChat($message, $userId);
        }
    }

    private function createNewChat(string $message, int $userId): void
    {
        $title = $this->titleService->generateTitle($message);
        $this->chat = $this->chatRepository->create([
            'user_id' => $userId,
            'name' => $title
        ]);
        $this->isNewChat = true;
    }

    private function getNeuralModel(string $modelName): Neural
    {
        return Neural::where('name', $modelName)->firstOrFail();
    }

    private function saveUserMessage(string $message): void
    {
        $this->messageRepository->create([
            'chat_id' => $this->chat->id,
            'message' => $message,
            'role' => 'user'
        ]);
    }

    private function streamResponse(int $userId): Response
    {
        return response()->stream(function () use ($userId) {
            $this->sendInitialInfo();
            $this->processStream();
            $this->finalizeStream($userId);
        }, 200, $this->getStreamHeaders());
    }

    private function sendInitialInfo(): void
    {
        echo "data: " . json_encode([
                'type' => 'chat_info',
                'chat_id' => $this->chat->id,
                'is_new_chat' => $this->isNewChat,
                'chat_name' => $this->chat->name
            ]) . "\n\n";
        flush();
    }

    private function processStream(): void
    {
        $conversation = $this->buildConversation();

        $response = Ollama::model($this->neural->name)
            ->options(['temperature' => $this->neural->temperature])
            ->stream(true)
            ->chat($conversation);

        Ollama::processStream($response->getBody(), function($data) {
            if (isset($data['message']['content'])) {
                $content = $data['message']['content'];
                $this->fullResponse .= $content;

                echo "data: " . json_encode([
                        'type' => 'content',
                        'content' => $content,
                        'full_response' => $this->fullResponse
                    ]) . "\n\n";
                flush();

                return !connection_aborted();
            }
            return true;
        });
    }

    private function buildConversation(): array
    {
        $lastMessages = $this->chatRepository->getLastMessages(
            $this->chat->id,
            $this->neural->countLastMessage ?? 10
        );

        $conversation = $lastMessages->map(fn($msg) => [
            'role' => $msg['role'],
            'content' => $msg['message']
        ])->toArray();

        array_unshift($conversation, [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ]);

        return $conversation;
    }

    private function getSystemPrompt(): string
    {
        return $this->neural->basePrompt->prompt ?? $this->getDefaultPrompt();
    }

    private function getDefaultPrompt(): string
    {
        return "Ты - полезный AI-ассистент. Отвечай на русском языке. Используй Markdown для форматирования.";
    }

    private function finalizeStream(int $userId): void
    {
        if (!empty($this->fullResponse)) {
            $this->messageRepository->create([
                'chat_id' => $this->chat->id,
                'message' => $this->fullResponse,
                'role' => 'assistant'
            ]);

            $this->chatRepository->updateLastMessageTime($this->chat->id);
            UserActivity::updateActivity($userId);

            echo "data: " . json_encode([
                    'type' => 'complete',
                    'full_response' => $this->fullResponse,
                    'chat_id' => $this->chat->id
                ]) . "\n\n";
            flush();
        }
    }

    private function getStreamHeaders(): array
    {
        return [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ];
    }
}
