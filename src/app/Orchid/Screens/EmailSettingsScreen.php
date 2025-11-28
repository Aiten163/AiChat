<?php

namespace App\Orchid\Screens;

use App\Models\Base_prompt as BasePrompt;
use Illuminate\Support\Facades\Storage;
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
        if ( Storage::exists('mail_settings.json')) {
             $mailSettings = json_decode(Storage::get('mail_settings.json'));
        }

        return [
            Layout::view('admin.emailSettings', [
                'emailData' => [
                    'login' => $mailSettings['mail_username'] ?? '',
                    'passwordIsSet' => isset($mailSettings['mail_password']),
                    'sender' => $mailSettings['sender'] ?? '',
                    'port' => $mailSettings['mail_port'] ?? '',
                    'theme' => $mailSettings['mail_message_theme'] ?? '',
                    'greeting' => $mailSettings['mail_message_theme'] ?? '',
                    'text' => $mailSettings['mail_message_text'] ?? '',
                ]
            ]),
        ];
    }

}
