<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:3',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Имя пользователя обязательно',
            'password.required' => 'Пароль обязателен',
        ];
    }
}
