<?php

namespace App\Services\Chat;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Neural;
use App\Models\UserActivity;
use App\Services\Neural\OllamaService;
use App\Services\Chat\ChatTitleService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatService
{
    private ?Chat $chat = null;
    private bool $isNewChat = false;
    private Neural $neural;
    private string $fullResponse = '';

    public function __construct(
        private readonly ChatTitleService $titleService,
        private readonly OllamaService $ollamaService
    ) {}

    public function processMessage(string $chatId, string $message, string $modelName, int $userId): Response
    {
        $this->neural = Neural::getOnName($modelName) ?? Neural::where('name', $modelName)->firstOrFail();
        $this->initializeChat($chatId, $message, $userId);

        $this->saveMessage($message, 'user');

        return $this->streamResponse($userId);
    }

    private function initializeChat(?string $chatId, string $message, int $userId): void
    {
        if (!$chatId || $chatId === 'new-chat') {
            $this->createNewChat($message, $userId);
            return;
        }

        $this->chat = Chat::find($chatId);

        if (!$this->chat) {
            $this->createNewChat($message, $userId);
        }
    }

    private function createNewChat(string $message, int $userId): void
    {
        $title = $this->titleService->generateTitle($message);
        $this->chat = Chat::create([
            'user_id' => $userId,
            'name' => $title,
            'show' => true,
        ]);
        $this->isNewChat = true;
    }

    private function saveMessage(string $message, string $role): void
    {
        ChatMessage::create([
            'chat_id' => $this->chat->id,
            'message' => $message,
            'role' => $role,
            'created_at' => now()
        ]);
    }

    private function streamResponse(int $userId): Response
    {
        return response()->stream(function () use ($userId) {
            $this->sendInitialInfo();
            $this->processOllamaStream();
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

    private function processOllamaStream(): void
    {
        $conversation = $this->buildConversation();

        $this->ollamaService->streamChat(
            model: $this->neural->name,
            conversation: $conversation,
            temperature: $this->neural->temperature,
            onContent: function(string $content) {
                $this->fullResponse .= $content;
                echo "data: " . json_encode([
                        'type' => 'content',
                        'content' => $content,
                        'full_response' => $this->fullResponse
                    ]) . "\n\n";
                flush();
            }
        );
    }

    private function buildConversation(): array
    {
        $lastMessages = $this->chat->getLastMessages($this->neural->countLastMessage ?? 10);

        $conversation = $lastMessages->map(fn($message) => [
            'role' => $message['role'],
            'content' => $message['message']
        ])->toArray();

        $systemPrompt = $this->neural->basePrompt?->prompt ?? $this->getDefaultPrompt();

        array_unshift($conversation, [
            'role' => 'system',
            'content' => $systemPrompt
        ]);

        return $conversation;
    }

    private function getDefaultPrompt(): string
    {
        return "Ты - полезный AI-ассистент. Отвечай на русском языке. Используй Markdown для форматирования.";
    }

    private function finalizeStream(int $userId): void
    {
        if (!empty($this->fullResponse)) {
            $this->saveMessage($this->fullResponse, 'assistant');

            $this->chat->update(['lastMessage' => now()]);
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
