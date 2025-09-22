<?php

namespace App\Orchid\Screens;

use App\Models\User;
use App\Orchid\Layouts\UsersTable;
use Orchid\Screen\Screen;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Toast;
use Illuminate\Http\Request;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\CheckBox;

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
            'users' => User::orderBy('id')->paginate(20)
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
        return 'Управление пользователями системы';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Добавить пользователя')
                ->icon('plus')
                ->modal('createUser')
                ->method('save')
                ->modalTitle('Добавление пользователя'),
        ];
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

            // Модальное окно для создания пользователя
            Layout::modal('createUser', Layout::rows([
                Input::make('user.name')
                    ->title('Имя')
                    ->placeholder('Введите имя')
                    ->required(),
                CheckBox::make('user.is_admin')
                    ->title('Администратор')
                    ->placeholder('Права администратора')
                    ->sendTrueOrFalse(),
            ]))->title('Добавить пользователя')->applyButton('Добавить'),

            // Модальное окно для редактирования пользователя
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
            User::findOrFail($userId)->delete();
            Toast::info('Пользователь удален');
        }
    }
}
