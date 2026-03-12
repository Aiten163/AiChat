<?php

namespace App\Orchid\Screens;

use App\Models\User;
use App\Models\UserActivity;
use App\Orchid\Layouts\UsersTable;
use Illuminate\Support\Facades\Cache;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Actions\Button;

class UserListScreen extends Screen
{
    public function query(): iterable
    {
        return [
            'users' => User::with('userActivity')
                ->leftJoin('userActivity', 'users.id', '=', 'userActivity.user_id')
                ->select('users.*', 'userActivity.number_messages', 'userActivity.lastLogin', 'userActivity.lastMessage')
                ->filters()
                ->defaultSort('id')
                ->paginate(20)
        ];
    }

    public function name(): ?string
    {
        return 'Пользователи';
    }

    public function description(): ?string
    {
        return 'Управление пользователями системы и отслеживание активности';
    }

    public function layout(): array
    {
        return [
            UsersTable::class,

            Layout::modal('editUser', Layout::rows([
                Input::make('user.id')->type('hidden'),
                Input::make('user.name')
                    ->title('Имя')
                    ->placeholder('Введите имя')
                    ->required(),
                CheckBox::make('user.is_admin')
                    ->title('Администратор')
                    ->placeholder('Права администратора')
                    ->sendTrueOrFalse(),
            ]))->async('asyncGetUser')->title('Редактирование пользователя')->applyButton('Сохранить'),
        ];
    }

    public function asyncGetUser(User $user): array
    {
        return [
            'user' => $user
        ];
    }

    public function save(Request $request): void
    {
        $data = $request->get('user');

        if (!empty($data['id'])) {
            $user = User::findOrFail($data['id']);
            $user->update([
                'name' => $data['name'],
                'is_admin' => $data['is_admin'] ?? false
            ]);

            Cache::tags(['users'])->forget('admins');

            Toast::success('Пользователь обновлен');
        } else {
            User::create([
                'name' => $data['name'],
                'is_admin' => $data['is_admin'] ?? false
            ]);

            Cache::tags(['users'])->forget('admins');

            Toast::success('Пользователь создан');
        }
    }

    public function remove(Request $request): void
    {
        $userId = $request->get('user_id');

        if ($userId) {
            UserActivity::where('user_id', $userId)->delete();
            User::findOrFail($userId)->delete();

            Cache::tags(['users'])->forget('admins');

            Toast::info('Пользователь удален');
        }
    }
}
