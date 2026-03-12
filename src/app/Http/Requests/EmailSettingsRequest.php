<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EmailSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'emailLogin' => 'nullable|email|max:255',
            'emailPassword' => 'nullable|string|max:255',
            'messageTheme' => 'nullable|string|max:255',
            'messageGreeting' => 'nullable|string|max:500',
            'messageText' => 'nullable|string|max:2000',
            'port' => 'nullable|integer|in:465,587,25',
            'sender' => 'nullable|string|max:255',
        ];
    }
}
