<?php

namespace App\Services\Chat;

use App\Models\ChatMessage;

class MessageRepository
{
    public function create(array $data): ChatMessage
    {
        return ChatMessage::create($data);
    }
}
