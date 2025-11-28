<?php

namespace App\Http\Controllers;

use App\Services\EmailSettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailSettingsController extends Controller
{
    public function store(Request $request) {
        $request->validate([
            'emailLogin' => 'email|nullable',
            'emailPassword' => 'string|nullable',
            'messageTheme' => 'string|nullable',
            'messageGreeting' => 'string|nullable',
            'messageText' => 'string|nullable',
            'port' => 'integer|nullable',
            'sender' => 'string|nullable',
        ]);

        $email = $request->emailLogin;
        $password = $request->emailPassword;
        $theme = $request->messageTheme;
        $greeting = $request->messageGreeting;
        $text = $request->messageText;
        $port = $request->port;
        $sender = $request->sender;
        EmailSettingsService::store($port,$email, $password, $theme, $greeting, $text, $sender);

        return redirect()->route('platform.emailSettings');
    }
}
