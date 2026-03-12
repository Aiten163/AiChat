<?php

namespace App\Services\Chat;

use App\Models\Chat;
use App\Models\ChatMessage;
use Illuminate\Database\Eloquent\Collection;

class ChatHistoryService
{
    public function getUserChats(int $userId): Collection
    {
        return Chat::where(['user_id' => $userId, 'show' => true])
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'lastMessage']);
    }

    public function getChatMessages(int $chatId, int $userId): Collection
    {
        $chat = Chat::where([
            'id' => $chatId,
            'show' => true,
            'user_id' => $userId
        ])->firstOrFail();

        return ChatMessage::where('chat_id', $chatId)
            ->select('message', 'role')
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
