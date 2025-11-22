<?php

namespace App\Orchid\Screens;

use App\Models\Base_prompt as BasePrompt;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Screen;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Group;
use Illuminate\Http\Request;

class EmailSettingsScreen extends Screen
{
    public $name = 'Настройка почты';
    public $description = '';
    public $permission = [];
    public function query(): array
    {
        return [];
    }

    public function layout(): array
    {
        $login = 'test ';
        return [
            Layout::view('admin.emailSettings', [
                'email' => [
                    'login' => $login
                ]
            ]),
        ];
    }

}
