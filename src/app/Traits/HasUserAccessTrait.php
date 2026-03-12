<?php

namespace App\Traits;

trait HasUserAccessTrait
{
    public function isOwnedBy(int $userId): bool
    {
        return $this->user_id === $userId;
    }

    public function authorizeUser(int $userId): void
    {
        if (!$this->isOwnedBy($userId)) {
            throw new \App\Exceptions\ChatAccessDeniedException('Доступ запрещен');
        }
    }
}
