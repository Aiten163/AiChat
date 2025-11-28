<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class ReportNotification extends Notification
{
    use Queueable;
    private User $user;
    private string $message;
    private string $imagePath;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, $message, $imagePath)
    {
        $this->user = $user;
        $this->message = $message;
        $this->imagePath = $imagePath;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', DashboardChannel::class];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line();
    }

    public function toDashboard(object $notifiable)
    {
        return (new DashboardMessage)
            ->title('Техническая поддержка')
            ->message('Сообщение: ' . $this->message . "\n От пользователя: " . $this->user->name);
    }
}
