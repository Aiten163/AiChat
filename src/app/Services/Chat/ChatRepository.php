<?php

namespace App\Services\Chat;

use App\Models\Chat;
use Illuminate\Support\Collection;

class ChatRepository
{
    public function find(int $id): ?Chat
    {
        return Chat::find($id);
    }

    public function create(array $data): Chat
    {
        return Chat::create($data);
    }

    public function updateLastMessageTime(int $chatId): bool
    {
        return Chat::where('id', $chatId)->update(['lastMessage' => now()]);
    }

    public function getLastMessages(int $chatId, int $limit = 10): Collection
    {
        return Chat::find($chatId)
            ?->getLastMessages($limit) ?? collect();
    }
}
