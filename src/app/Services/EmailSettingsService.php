<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\Neural;
use App\Models\UserActivity;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class EmailSettingsService
{
    public static function store($port = 465, $email = '', $password='', $theme='', $greeting='', $text='', $sender=''): void
    {
        $mailSettings = [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.yandex.ru',
            'mail_port' => $port,
            'mail_username' => $email,
            'mail_password' => $password,
            'mail_encryption' => 'ssl',
            'mail_from_address' => $email,
            'mail_from_name' => $sender,
            'mail_message_theme' => $theme,
            'mail_message_greeting' => $greeting,
            'mail_message_text' => $text,
        ];
        Storage::put(
            'mail_settings.json',
            json_encode($mailSettings, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
        );
    }
}

