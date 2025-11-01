<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Neural;
use App\Models\UserActivity;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatService
{
    protected Chat|null $chat;
    protected string $text;
    protected Neural $neural;
    protected int $userID;
    protected int $temperature = 50;
    protected ChatTitleService $titleService;
    protected Ollama $OllamaModel;

    protected bool $isNewChat = false;

    public function __construct($chatID, $text, $neural_name, $userID)
    {
        $this->titleService = new ChatTitleService();
        $this->text = $text;
        $this->userID = $userID;
        $this->neural = Neural::where('name', $neural_name)->first();
        $this->temperature = $this->neural->temperature;
        $this->OllamaModel = Ollama::model($this->neural->name);

        // Если chatID = 'new-chat' или пустой, создаем новый чат
        if (!$chatID || $chatID === 'new-chat') {
            $chatTitle = $this->titleService->generateTitle($text);

            $this->chat = Chat::create([
                'user_id' => $userID,
                'name' => $chatTitle
            ]);
            $this->isNewChat = true;
        } else {
            // Ищем существующий чат
            $this->chat = Chat::find($chatID);
            if (!$this->chat) {
                // Если чат не найден, создаем новый
                $chatTitle = $this->titleService->generateTitle($text);

                $this->chat = Chat::create([
                    'user_id' => $userID,
                    'name' => $chatTitle
                ]);
                $this->isNewChat = true;
            }
        }
    }

    public function getResponse()
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

        // Добавляем системный промпт с инструкциями
        $systemPrompt = [
            'role' => 'system',
            'content' => $this->getSystemPrompt()
        ];

        // Вставляем системный промпт в начало конверсации
        array_unshift($conversation, $systemPrompt);

        $response = $this->OllamaModel
            ->options([
                'temperature' => $this->temperature,
            ])
            ->chat($conversation);

        $data = [ // Create current request and response from neural
            [
                'chat_id' => $this->chat->id,
                'message' => $this->text,
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ],
            [
                'chat_id' => $this->chat->id,
                'message' => $response['message']['content'],
                'role' => $response['message']['role'],
                'created_at' => date('Y-m-d H:i:s')
            ]
        ];
        ChatMessage::insert($data);

        $this->chat->update(['lastMessage' => now()]);

        UserActivity::updateActivity(Auth::id());

        // Возвращаем ответ и информацию о чате
        return [
            'response' => $response['message']['content'],
            'chat_id' => $this->chat->id,
            'is_new_chat' => $this->isNewChat,
            'chat_name' => $this->chat->name
        ];
    }


    /**
     * Генерирует системный промпт с инструкциями для нейросети
     */
    private function getSystemPrompt(): string
    {
        if(isset($this->neural->basePrompt)) {
            return $this->neural->basePrompt;
        } else
            return
            "Ты - полезный AI-ассистент. Строго соблюдай следующие правила:
        1. **Язык ответов**: Всегда отвечай на русском языке, если пользователь явно не запросил другой язык.
        2. **Форматирование Markdown**: Всегда используй Markdown-разметку для форматирования ответов:
           - Заголовки разных уровней (# ## ###)
           - **Жирный текст** для важных понятий
           - *Курсив* для акцентов
           - Списки (нумерованные и ненумерованные)
           - Блоки кода с указанием языка
           - Таблицы для структурированных данных
           - Цитаты (>)
        3. **Оформление кода**: Для любого фрагмента кода:
           - Всегда используй блоки кода с тройными апострофами и указанием языка
           - Указывай язык программирования после апострофов (```python, ```php, ```javascript и т.д.)
           - Комментируй сложные части кода на русском языке
           - Для небольших фрагментов используй `встроенный код`
        4. **Структура ответа**:
           - Дели ответ на логические разделы
           - Используй заголовки для основных тем
           - Выделяй ключевые моменты жирным шрифтом
           - Для пошаговых инструкций используй нумерованные списки
        Следуй этим правилам во всех ответах.";
    }

    public function getChatId()
    {
        return $this->chat->id;
    }

    public function isNewChat()
    {
        return $this->isNewChat;
    }
}
