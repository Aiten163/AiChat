<?php

namespace App\Orchid\Layouts;

use App\Models\User;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class UsersTable extends Table
{
    /**
     * Data source.
     *
     * @var string
     */
    protected $target = 'users';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID')
                ->sort()
                ->width('80px')
                ->filter(TD::FILTER_NUMERIC),

            TD::make('name', 'Имя')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->width('200px'),

            TD::make('is_admin', 'Администратор')
                ->sort()
                ->width('150px')
                ->render(function (User $user) {
                    return $user->is_admin ? 'Да' : 'Нет';
                }),

            TD::make('activity.number_messages', 'Сообщения')
                ->sort()
                ->width('150px')
                ->render(function (User $user) {
                    return $user->activity ? $user->activity->number_messages : 0;
                }),

            TD::make('activity.lastLogin', 'Последний вход')
                ->sort()
                ->width('200px')
                ->render(function (User $user) {
                    return $user->activity && $user->activity->lastLogin
                        ? $user->activity->lastLogin->format('d.m.Y H:i')
                        : 'Никогда';
                }),

            TD::make('activity.lastMessage', 'Последнее сообщение')
                ->sort()
                ->width('200px')
                ->render(function (User $user) {
                    return $user->activity && $user->activity->lastMessage
                        ? $user->activity->lastMessage->format('d.m.Y H:i')
                        : 'Нет сообщений';
                }),

            TD::make('action', 'Действия')
                ->alignRight()
                ->width('120px')
                ->render(function (User $user) {
                    return ModalToggle::make("")
                        ->modal('editUser')
                        ->icon('pencil')
                        ->method('save')
                        ->modalTitle('Редактирование пользователя ' . $user->name)
                        ->asyncParameters([
                            'user' => $user->id
                        ]);
                }),

            TD::make('delete', '')
                ->alignRight()
                ->width('50px')
                ->render(function (User $user) {
                    return Button::make("")
                        ->icon('trash')
                        ->method('remove', [
                            'user_id' => $user->id
                        ])
                        ->confirm('Вы уверены, что хотите удалить этого пользователя?');
                })
        ];
    }
}
