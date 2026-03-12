<?php

namespace App\Services\Chat;

use App\Models\Chat;
use App\Exceptions\ChatAccessDeniedException;

class ChatManagementService
{
    public function rename(Chat $chat, string $newName): string
    {
        $this->checkAccess($chat);

        $chat->update(['name' => trim($newName)]);

        return $chat->name;
    }

    public function softDelete(Chat $chat): bool
    {
        $this->checkAccess($chat);

        return $chat->update(['show' => false]);
    }

    private function checkAccess(Chat $chat): void
    {
        if ($chat->user_id !== auth()->id()) {
            throw new ChatAccessDeniedException('Доступ запрещен');
        }
    }
}
