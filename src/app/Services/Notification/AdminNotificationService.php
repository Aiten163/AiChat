<?php

namespace App\Services\Notification;

use App\Models\User;
use Illuminate\Notifications\Notification;

class AdminNotificationService
{
    public function notifyAdmins(Notification $notification): void
    {
        $admins = User::getAdmins();
        \Notification::send($admins, $notification);
    }

    public function notifyAdminsAboutViolation(int $userId, string $message, string $error): void
    {
        $admins = User::getAdmins();
        \Notification::send($admins, new \App\Notifications\ProhibitedMessageGet($userId, $message, $error));
    }
}
