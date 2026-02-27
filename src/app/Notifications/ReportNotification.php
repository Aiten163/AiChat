<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class ReportNotification extends Notification
{
    use Queueable;

    private $user;
    private $message;
    private $imagePath;

    public function __construct($user, $message, $imagePath)
    {
        $this->user = $user;
        $this->message = $message;
        $this->imagePath = $imagePath;
    }

    public function via(object $notifiable): array
    {
        return [DashboardChannel::class];
    }

    public function toDashboard(object $notifiable): array
    {
        $userName = $this->user ? $this->user->name : 'Анонимный пользователь';
        $shortMessage = \Str::limit($this->message, 50);

        $detailUrl = route('platform.reports.detail', [
            'notification' => $this->id
        ]);

        return [
            'title' => 'Новое обращение в техподдержку',
            'message' => "Пользователь: {$userName}\nСообщение: {$shortMessage}",
            'action' => $detailUrl,
            'type' => 'info',

            'report_data' => [
                'user_name' => $this->user ? $this->user->name : 'Анонимный пользователь',
                'user_id' => $this->user ? $this->user->id : null,
                'message' => $this->message,
                'image_path' => $this->imagePath,
            ]
        ];
    }
}
