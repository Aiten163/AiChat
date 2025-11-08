<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class ProhibitedMessageGet extends Notification
{
    use Queueable;

    private string $message;
    /**
     * Create a new notification instance.
     */
    public function __construct($data)
    {
        $this->message = 123;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [DashboardChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDashboard(object $notifiable)
    {
        return (new DashboardMessage)
            ->title('Получен запрещенный запрос!')
            ->message('')
            ->action('Notification Action', url('/'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
