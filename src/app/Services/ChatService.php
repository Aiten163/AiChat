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
    protected string $neural_name;
    protected int $userID;
    protected int $temperature = 50;
    protected bool $isNewChat = false;

    public function __construct($chatID, $text, $neural_name, $userID)
    {
        // Если chatID = 'new-chat' или пустой, создаем новый чат
        if (!$chatID || $chatID === 'new-chat') {
            $this->chat = Chat::create([
                'user_id' => $userID,
                'name' => substr($text, 0, 20) . '...'
            ]);
            $this->isNewChat = true;
            Log::info('Created new chat with ID: ' . $this->chat->id);
        } else {
            // Ищем существующий чат
            $this->chat = Chat::find($chatID);
            if (!$this->chat) {
                // Если чат не найден, создаем новый
                $this->chat = Chat::create([
                    'user_id' => $userID,
                    'name' => substr($text, 0, 20) . '...'
                ]);
                $this->isNewChat = true;
                Log::warning('Chat not found, created new chat with ID: ' . $this->chat->id);
            }
        }

        $this->text = $text;
        $this->neural_name = $neural_name;
        $this->temperature = $this->getTemperature($neural_name);
        $this->userID = $userID;
    }

    public function getResponse()
    {
        $currentMessage = collect([
            'message' => $this->text,
            'role' => 'user'
        ]);

        $countLastMessage = Neural::where('name', $this->neural_name)->value('countLastMessage');
        $lastMessages = $this->chat->getLastMessages($countLastMessage)->push($currentMessage);
        $conversation = $lastMessages->map(function ($message) {
            return [
                'role' => $message['role'],
                'content' => $message['message']
            ];
        })->toArray();

        $test['message']['content'] = time();
        $test['message']['role'] = 'helper';
        $response = $test; //Ollama::model($this->neural_name)->chat($conversation);

        Log::info('Processing chat ID: ' . $this->chat->id);

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

        UserActivity::updateActivity(Auth::id());

        // Возвращаем ответ и информацию о чате
        return [
            'response' => $response['message']['content'],
            'chat_id' => $this->chat->id,
            'is_new_chat' => $this->isNewChat,
            'chat_name' => $this->chat->name
        ];
    }

    private function getTemperature($neural_name): int
    {
        return Neural::where('name', $neural_name)->value('temperature');
    }

    // Геттер для получения ID чата (можно использовать отдельно)
    public function getChatId()
    {
        return $this->chat->id;
    }

    // Геттер для проверки, новый ли это чат
    public function isNewChat()
    {
        return $this->isNewChat;
    }
}
