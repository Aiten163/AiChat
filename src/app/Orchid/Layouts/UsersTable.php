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
                ->width('70px')
                ->filter(TD::FILTER_NUMERIC),

            TD::make('name', 'Имя')
                ->sort()
                ->filter(TD::FILTER_TEXT)
                ->width('150px'),

            TD::make('is_admin', 'Администратор')
                ->sort()
                ->width('70px')
                ->filter(TD::FILTER_SELECT, [
                    1 => 'Да',
                    0 => 'Нет',
                ])
                ->render(function (User $user) {
                    return $user->is_admin ? 'Да' : 'Нет';
                }),

            TD::make('number_messages', 'Кол-во сообщений')
                ->width('70px')
                ->render(function (User $user) {
                    return $user->userActivity->number_messages;
                }),

            TD::make('lastLogin', 'Последний вход')
                ->width('70px')
                ->render(function (User $user) {
                    return $user->userActivity && $user->userActivity->lastLogin
                        ? $user->userActivity->lastLogin->format('d.m.Y H:i')
                        : 'Никогда';
                }),

            TD::make('lastMessage', 'Последнее сообщение')
                ->width('170px')
                ->render(function (User $user) {
                    return $user->userActivity && $user->userActivity->lastMessage
                        ? $user->userActivity->lastMessage->format('d.m.Y H:i')
                        : 'Нет сообщений';
                }),

            TD::make('action', 'Действия')
                ->alignRight()
                ->width('50px')
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
