<?php

namespace App\Orchid\Screens;

use App\Models\User;
use App\Models\UserActivity;
use App\Orchid\Layouts\UsersTable;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\Button;

class UserListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
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

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Пользователи';
    }

    /**
     * The screen's description.
     *
     * @return string|null
     */
    public function description(): ?string
    {
        return 'Управление пользователями системы и отслеживание активности';
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
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

    /**
     * Async data for edit modal
     */
    public function asyncGetUser(User $user): array
    {
        return [
            'user' => $user
        ];
    }

    /**
     * Save or update user
     */
    public function save(Request $request): void
    {
        $data = $request->get('user');

        if (!empty($data['id'])) {
            // Update existing user
            $user = User::findOrFail($data['id']);
            $user->update([
                'name' => $data['name'],
                'is_admin' => $data['is_admin'] ?? false
            ]);
            Toast::success('Пользователь обновлен');
        } else {
            // Create new user
            User::create([
                'name' => $data['name'],
                'is_admin' => $data['is_admin'] ?? false
            ]);
            Toast::success('Пользователь создан');
        }
    }

    /**
     * Remove user
     */
    public function remove(Request $request): void
    {
        $userId = $request->get('user_id');

        if ($userId) {
            // Удаляем активность пользователя перед удалением самого пользователя
            UserActivity::where('user_id', $userId)->delete();
            User::findOrFail($userId)->delete();
            Toast::info('Пользователь удален');
        }
    }

}
