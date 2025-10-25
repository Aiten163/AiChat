<?php

namespace App\Orchid\Layouts;

use App\Models\ChatMessage;
use Nette\Utils\Html;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class MessagesTable extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'messages';

    /**
     * @return TD[]
     */
    protected function columns(): array
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(function (ChatMessage $message) {
                    return $message->id;
                }),

            TD::make('message', 'Текст сообщения')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->render(function (ChatMessage $message) {
                    return view('admin.message-preview', [
                        'shortMessage' => \Illuminate\Support\Str::limit($message->message, 75),
                        'fullMessage' => $message->message,
                        'messageId' => $message->id,
                    ])->render();
                }),

            TD::make('chat_id', 'Чат')
                ->width(100)
                ->sort()
                ->filter(TD::FILTER_NUMERIC)
                ->render(function (ChatMessage $message) {
                    return "{$message->chat_id} - {$message->chat->name}";
                }),

            TD::make('user_id', 'Отправитель')
                ->sort()
                ->filter(TD::FILTER_NUMERIC)
                ->render(function (ChatMessage $message) {
                    $user = $message->chat->user;
                    return $user->name;
                }),

            TD::make('role', 'Роль')
                ->sort()
                ->filter(TD::FILTER_SELECT, [
                    'user' => 'Пользователь',
                    'assistant' => 'Ассистент',
                    'system' => 'Система',
                ])
                ->render(function (ChatMessage $message) {
                    return $message->role; // Убрал Str::limit, чтобы показывать полную роль
                }),

            TD::make('created_at', 'Дата и время')
            ->sort()
            ->filter(TD::FILTER_DATE_RANGE)
            ->render(function (ChatMessage $message) {
                return $message->created_at->format('d.m.Y H:i:s');
            }),
        ];
    }
}
