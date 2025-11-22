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
        $mailSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.yandex.ru',
            'mail_port' => 465,
            'mail_username' => 'aiten1663@yandex.ru',
            'mail_password' => 'ealayxitqpkfzbal',
            'mail_encryption' => 'ssl',
            'mail_from_address' => 'aiten1663@yandex.ru',
            'mail_from_name' => 'AiChat',
            'mail_message_theme' => '',
            'mail_message_greeting' => '',
            'mail_message_text' => '',
        ];

        $login = 'test ';
        return [
            Layout::view('admin.emailSettings', [
                'emailData' => [
                    'login' => $mailSettings['mail_username'],
                    'passwordIsSet' => isset($mailSettings['mail_password']),
                    '' => $mailSettings['mail_username'],
                ]
            ]),
        ];
    }

}
