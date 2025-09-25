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
                ->width('100px')
                ->filter(TD::FILTER_NUMERIC),

            TD::make('name', 'Имя')
                ->sort()
                ->filter(TD::FILTER_TEXT),

            TD::make('is_admin', 'Администратор')
                ->sort()
                ->render(function (User $user) {
                    return $user->is_admin ?  'Да' : 'Нет';
                }),

            TD::make('action', '')
                ->cantHide()
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

            TD::make('action', '')
                ->cantHide()
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
