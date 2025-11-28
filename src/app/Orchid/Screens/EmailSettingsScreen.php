<?php

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\Storage;
use Orchid\Support\Facades\Toast;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;
use Illuminate\Http\Request;

class EmailSettingsScreen extends Screen
{
    public $name = 'Настройка почты';
    public $description = 'Настройка параметров электронной почты';

    public function query(): array
    {
        $mailSettings = [];
        if (Storage::exists('mail_settings.json')) {
            $mailSettings = json_decode(Storage::get('mail_settings.json'), true);
        }

        return [
            'emailData' => [
                'login' => $mailSettings['mail_username'] ?? '',
                'passwordIsSet' => isset($mailSettings['mail_password']),
                'sender' => $mailSettings['sender'] ?? '',
                'port' => $mailSettings['mail_port'] ?? '',
                'theme' => $mailSettings['mail_message_theme'] ?? '',
                'greeting' => $mailSettings['mail_message_greeting'] ?? '', // исправлено: было theme вместо greeting
                'text' => $mailSettings['mail_message_text'] ?? '',
            ]
        ];
    }

    public function layout(): array
    {
        return [
            Layout::view('admin.emailSettings'),
        ];
    }

    // ДОБАВЬТЕ ЭТОТ МЕТОД ДЛЯ ОБРАБОТКИ ФОРМЫ
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emailLogin' => 'required|email',
            'emailPassword' => 'sometimes|string',
            'sender' => 'required|string',
            'port' => 'required|integer',
            'messageTheme' => 'required|string',
            'messageGreeting' => 'required|string',
            'messageText' => 'required|string',
        ]);

        // Подготовка данных для сохранения
        $mailSettings = [
            'mail_username' => $validated['emailLogin'],
            'sender' => $validated['sender'],
            'mail_port' => $validated['port'],
            'mail_message_theme' => $validated['messageTheme'],
            'mail_message_greeting' => $validated['messageGreeting'],
            'mail_message_text' => $validated['messageText'],
        ];

        // Сохраняем пароль только если он был изменен
        if (!empty($validated['emailPassword'])) {
            $mailSettings['mail_password'] = $validated['emailPassword'];
        }

        // Сохраняем в файл
        Storage::put('mail_settings.json', json_encode($mailSettings, JSON_PRETTY_PRINT));

        Toast::success('Настройки почты успешно сохранены!');

        return back();
    }

    // Или если вы хотите использовать отдельный метод для маршрута
    public function saveEmailSettings(Request $request)
    {
        return $this->store($request);
    }
}
