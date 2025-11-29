<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;
use Orchid\Platform\Notifications\DashboardChannel;
use Orchid\Platform\Notifications\DashboardMessage;

class ProhibitedMessageGet extends Notification
{
    use Queueable;

    private string $message;
    private User $user;
    private string $reason;
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $message, $reason)
    {
        $this->user = User::find($user);
        $this->message = $message;
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return [DashboardChannel::class, 'mail'];
    }

    public function toDashboard(object $notifiable)
    {
        return (new DashboardMessage)
            ->title('Получен запрещенный запрос! ' . $this->reason)
            ->message('Сообщение: ' . $this->message . " от пользователя: " . $this->user->name . " " . now());
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Нарушение правил!')
            ->greeting("Внимание, $this->user->name")
            ->line("")
            ->line("");
    }
}
