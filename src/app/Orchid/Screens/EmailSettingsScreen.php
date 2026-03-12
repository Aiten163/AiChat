<?php

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\Cache;
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

    private const CACHE_KEY = 'mail_settings';

    private function getMailSettings()
    {
        if (Cache::tags(['settings'])->has(self::CACHE_KEY)) {
            return Cache::tags(['settings'])->get(self::CACHE_KEY);
        }

        if (Storage::exists('mail_settings.json')) {
            $settings = json_decode(Storage::get('mail_settings.json'), true);
            Cache::tags(['settings'])->put(self::CACHE_KEY, $settings, 3600);
            return $settings;
        }

        return [];
    }

    public function query(): array
    {
        $mailSettings = $this->getMailSettings();

        return [
            'emailLogin' => $mailSettings['mail_username'] ?? '',
            'sender' => $mailSettings['sender'] ?? '',
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
            ]),

            Layout::rows([
                Label::make('')
                    ->title('Настройка для сообщений с нарушением информационной безопасности пользователям')
                    ->class('fw-bold fs-5'),
                Input::make('messageTheme')
                    ->title('Тема сообщения')
                    ->placeholder('Введите тему сообщения'),

                Input::make('messageGreeting')
                    ->title('Приветствие')
                    ->placeholder('*Приветствие*, *Имя пользователя*'),

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
            'messageTheme' => 'string|nullable',
            'messageGreeting' => 'string|nullable',
            'messageText' => 'string|nullable',
        ]);

        $currentSettings = $this->getMailSettings();

        $mailSettings = [
            'mail_username' => $validated['emailLogin'] ?? $currentSettings['mail_username'] ?? '',
            'sender' => $validated['sender'] ?? $currentSettings['sender'] ?? '',
            'mail_message_theme' => $validated['messageTheme'] ?? $currentSettings['mail_message_theme'] ?? '',
            'mail_message_greeting' => $validated['messageGreeting'] ?? $currentSettings['mail_message_greeting'] ?? '',
            'mail_message_text' => $validated['messageText'] ?? $currentSettings['mail_message_text'] ?? '',

            'mail_host' => 'smtp.yandex.ru',
            'mail_encryption' => 'ssl',
            'mail_port' => 465
        ];

        if (!empty(trim($validated['emailPassword'] ?? ''))) {
            $mailSettings['mail_password'] = $validated['emailPassword'];
        } else {
            $mailSettings['mail_password'] = $currentSettings['mail_password'] ?? '';
        }

        Storage::put('mail_settings.json', json_encode($mailSettings, JSON_PRETTY_PRINT));

        Cache::tags(['settings'])->forget(self::CACHE_KEY);

        Toast::success('Настройки почты успешно сохранены!');
        return back();
    }
}
