<?php

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Orchid\Screen\Fields\Label;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Actions\Button;
use Illuminate\Http\Request;

class EmailSettingsScreen extends Screen
{
    public $name = 'Настройка почты';
    public $description = 'Настройка параметров электронной почты';

    private function getMailSettings()
    {
        if (Storage::exists('mail_settings.json')) {
            return json_decode(Storage::get('mail_settings.json'), true);
        }
        return [];
    }

    public function query(): array
    {
        $mailSettings = $this->getMailSettings();

        return [
            'emailLogin' => $mailSettings['mail_username'] ?? '',
            'sender' => $mailSettings['sender'] ?? '',
            'port' => $mailSettings['mail_port'] ?? '',
            'messageTheme' => $mailSettings['mail_message_theme'] ?? '',
            'messageGreeting' => $mailSettings['mail_message_greeting'] ?? '',
            'messageText' => $mailSettings['mail_message_text'] ?? '',
            'passwordIsSet' => isset($mailSettings['mail_password']),
        ];
    }

    public function layout(): array
    {
        return [
            Layout::rows([
                Input::make('emailLogin')
                    ->type('email')
                    ->title('Email')
                    ->placeholder('Введите email'),

                Input::make('emailPassword')
                    ->type('password')
                    ->title('Пароль')
                    ->placeholder($this->query()['passwordIsSet'] ? 'Обновить пароль' : 'Установите пароль')
                    ->help('Оставьте пустым, если не хотите менять пароль')
                    ->autocomplete('new-password'),

                Input::make('sender')
                    ->title('Имя отправителя')
                    ->placeholder('Ai Chat'),

                Input::make('port')
                    ->type('number')
                    ->title('Порт')
                    ->placeholder('Введите порт')
                    ->help('SSL 465 | без SSL 587'),
            ]),

                Layout::rows([
                    Label::make('')
                        ->title('Настройка для сообщений с нарушением информационной безопасности')
                        ->class('fw-bold fs-5'),
                    Input::make('messageTheme')
                        ->title('Тема сообщения')
                        ->placeholder('Введите тему сообщения'),

                    Input::make('messageGreeting')
                        ->title('Приветствие')
                        ->placeholder('*Приветствие*, *Имя пользователя*')
                        ->autofocus(false),

                    TextArea::make('messageText')
                        ->title('Текст сообщения')
                        ->rows(5)
                        ->placeholder('Введите текст сообщения'),
                ]),

            Layout::rows([
                Button::make('Сохранить')
                    ->method('store')
                    ->class('btn')
                    ->icon('bs.check-circle'),
            ]),
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'emailLogin' => 'email|nullable',
            'emailPassword' => 'sometimes|string|nullable',
            'sender' => 'string|nullable',
            'port' => 'integer|nullable',
            'messageTheme' => 'string|nullable',
            'messageGreeting' => 'string|nullable',
            'messageText' => 'string|nullable',
        ]);

        $currentSettings = $this->getMailSettings();

        $mailSettings = [
            'mail_username' => $validated['emailLogin'] ?? $currentSettings['mail_username'] ?? '',
            'sender' => $validated['sender'] ?? $currentSettings['sender'] ?? '',
            'mail_port' => $validated['port'] ?? $currentSettings['mail_port'] ?? '',
            'mail_message_theme' => $validated['messageTheme'] ?? $currentSettings['mail_message_theme'] ?? '',
            'mail_message_greeting' => $validated['messageGreeting'] ?? $currentSettings['mail_message_greeting'] ?? '',
            'mail_message_text' => $validated['messageText'] ?? $currentSettings['mail_message_text'] ?? '',
        ];

        if (!empty(trim($validated['emailPassword'] ?? ''))) {
            $mailSettings['mail_password'] = $validated['emailPassword'];
        } else {
            $mailSettings['mail_password'] = $currentSettings['mail_password'] ?? '';
        }

        Storage::put('mail_settings.json', json_encode($mailSettings, JSON_PRETTY_PRINT));

        \Cache::forget('mail_settings');

        Toast::success('Настройки почты успешно сохранены!');

        return back();
    }
}
