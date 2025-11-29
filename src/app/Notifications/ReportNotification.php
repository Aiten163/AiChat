<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class ReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private $user;
    private $message;
    private $imagePath;

    /**
     * Create a new notification instance.
     */
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

    public function toDashboard(object $notifiable)
    {
        $userName = $this->user ? $this->user->name : 'Анонимный пользователь';

        $message = (new DashboardMessage)
            ->title('Новое обращение в техподдержку')
            ->message("
 Пользователь: {$userName}
 Сообщение: {$this->message}
            ");

        if ($this->imagePath) {
            $filename = basename($this->imagePath);
            $imageUrl = route('private.reports.image', ['filename' => $filename]);
            $message->action('🖼️ Просмотреть изображение', $imageUrl);
        }

        return $message;
    }
}
