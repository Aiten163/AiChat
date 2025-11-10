<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Neural;
use App\Models\UserActivity;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class ChatService
{
    protected Chat|null $chat;
    protected string $text = '';
    protected Neural $neural;
    protected int $userID;
    protected int $temperature = 50;
    protected ChatTitleService $titleService;
    protected mixed $OllamaModel;
    protected bool $isNewChat = false;

    public function __construct($chatID, $text, $neural_name, $userID)
    {
        $this->neural = Neural::where('name', $neural_name)->first();
        $this->titleService = new ChatTitleService();
        $this->text = $text;
        $this->userID = $userID;
        $this->temperature = $this->neural->temperature;
        $this->OllamaModel = Ollama::model($this->neural->name);
        $this->createOrFindChat($chatID);
    }

    /**
     * Основной метод - возвращает Response для streaming
     */
    public function processMessage()
    {
        // Подготавливаем конверсацию
        $conversation = $this->prepareConversation();

        // Сохраняем сообщение пользователя
        ChatMessage::create([
            'chat_id' => $this->chat->id,
            'message' => $this->text,
            'role' => 'user',
            'created_at' => now()
        ]);

        // Создаем потоковый ответ
        return response()->stream(function () use ($conversation) {
            $this->streamWithChatHistory($conversation);
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Потоковая передача с полной историей чата
     */
    private function streamWithChatHistory(array $conversation): void
    {
        $fullResponse = '';

        try {

            echo "data: " . json_encode([
                    'type' => 'chat_info',
                    'chat_id' => $this->chat->id,
                    'is_new_chat' => $this->isNewChat,
                    'chat_name' => $this->chat->name
                ]) . "\n\n";
            flush();
            // Получаем ответ от Ollama
            $response = $this->OllamaModel
                ->options([
                    'temperature' => $this->temperature,
                ])
                ->stream(true)
                ->chat($conversation);

            // Обрабатываем поток
            $responses = Ollama::processStream($response->getBody(), function($data) use (&$fullResponse) {
                if (isset($data['message']['content'])) {
                    $content = $data['message']['content'];
                    $fullResponse .= $content;

                    echo "data: " . json_encode([
                            'type' => 'content',
                            'content' => $content,
                            'full_response' => $fullResponse
                        ]) . "\n\n";
                    flush();

                    if (connection_aborted()) {
                        return false;
                    }
                }
                return true;
            });

            // Сохраняем полный ответ
            if (!empty($fullResponse)) {
                $this->saveFinalResponse($fullResponse);

                echo "data: " . json_encode([
                        'type' => 'complete',
                        'full_response' => $fullResponse,
                        'chat_id' => $this->chat->id,
                        'is_new_chat' => $this->isNewChat,
                        'chat_name' => $this->chat->name
                    ]) . "\n\n";
                flush();
            } else {
                throw new \Exception('Пустой ответ от нейросети');
            }

        } catch (\Exception $e) {
            Log::error('Stream error: ' . $e->getMessage());

            echo "data: " . json_encode([
                    'type' => 'error',
                    'error' => $e->getMessage(),
                    'chat_id' => $this->chat->id ?? null
                ]) . "\n\n";
            flush();
        }
    }

    /**
     * Подготовка конверсации с усиленным системным промптом
     */
    private function prepareConversation(): array
    {
        $currentMessage = collect([
            'message' => $this->text,
            'role' => 'user'
        ]);

        $countLastMessage = Neural::where('name', $this->neural->name)->value('countLastMessage');
        $lastMessages = $this->chat->getLastMessages($countLastMessage)->push($currentMessage);

        $conversation = $lastMessages->map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => $message['message']
            ];
        })->toArray();

        // Добавляем системный промпт в начало
        $conversation = $this->addSystemPromptAsFirst($conversation);

        return $conversation;
    }

    /**
     * Добавляем системный промпт в НАЧАЛО
     */
    private function addSystemPromptAsFirst(array $conversation): array
    {
        $systemPrompt = [
            'role' => 'system',
            'content' => $this->getEnhancedSystemPrompt()
        ];

        array_unshift($conversation, $systemPrompt);

        return $conversation;
    }

    /**
     * Усиленный системный промпт
     */
    private function getEnhancedSystemPrompt(): string
    {
        if (isset($this->neural->basePrompt)) {
            return $this->neural->basePrompt->prompt;
        }

        return "Ты - полезный AI-ассистент. Строго соблюдай следующие правила:

**ЯЗЫК ОТВЕТОВ:**
- ВСЕГДА отвечай ТОЛЬКО на РУССКОМ языке
- Даже если пользователь пишет на английском - отвечай на русском
- Это строгое правило без исключений
- Никогда не переключайся на английский язык
- Все примеры кода, объяснения и ответы должны быть на русском

**ФОРМАТИРОВАНИЕ:**
- Используй Markdown-разметку
- Заголовки, списки, код-блоки
- Выделяй важное жирным шрифтом

**ВАЖНО:** Эти правила действуют на ВСЕ ответы без исключений! Нарушение этих правил недопустимо.";
    }

    /**
     * Сохраняем финальный ответ
     */
    private function saveFinalResponse(string $response): void
    {
        ChatMessage::create([
            'chat_id' => $this->chat->id,
            'message' => $response,
            'role' => 'assistant',
            'created_at' => now()
        ]);

        $this->chat->update(['lastMessage' => now()]);
        UserActivity::updateActivity(Auth::id());
    }

    private function createOrFindChat($chatID)
    {
        if (!$chatID || $chatID === 'new-chat') {
            $chatTitle = $this->titleService->generateTitle($this->text);
            $this->chat = Chat::create([
                'user_id' => $this->userID,
                'name' => $chatTitle
            ]);
            $this->isNewChat = true;
        } else {
            $this->chat = Chat::find($chatID);
            if (!$this->chat) {
                $chatTitle = $this->titleService->generateTitle($this->text);
                $this->chat = Chat::create([
                    'user_id' => $this->userID,
                    'name' => $chatTitle
                ]);
                $this->isNewChat = true;
            } else {
                $this->isNewChat = false; // ⚡ Важно: указываем что чат существующий
            }
        }
    }
}
