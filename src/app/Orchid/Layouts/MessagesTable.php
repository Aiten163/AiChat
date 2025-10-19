<?php

namespace App\Orchid\Layouts;

use App\Models\ChatMessage;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\Repository;

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
                ->render(function (ChatMessage $message) {
                    return \Illuminate\Support\Str::limit($message->message, 100);
                }),

            TD::make('chat_id', 'Чат')
                ->render(function (ChatMessage $message) {
                    return "{$message->chat_id} - {$message->chat->name}";
                })
                ->sort(),

            TD::make('user_id', 'Отправитель')
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
                    return \Illuminate\Support\Str::limit($message->role, 100);
                }),

            TD::make('created_at', 'Дата и время')
                ->sort()
                ->render(function (ChatMessage $message) {
                    return $message->created_at->format('d.m.Y H:i:s');
                }),

//            TD::make('actions', 'Действия')
//                ->render(function (ChatMessage $message) {
//                    return Link::make('Просмотреть')
//                        ->route('platform.messages', $message->id)
//                        ->icon('eye');
//                }),
        ];
    }
}
