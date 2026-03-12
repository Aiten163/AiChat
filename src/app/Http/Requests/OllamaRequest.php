<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OllamaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => 'required|string|max:5000',
            'chatID' => 'nullable|string|max:20',
            'model' => 'required|string|max:100|exists:neurals,name',
        ];
    }

    public function messages(): array
    {
        return [
            'prompt.required' => 'Введите сообщение',
            'prompt.max' => 'Сообщение не может быть длиннее 5000 символов',
            'model.exists' => 'Выбранная модель недоступна',
        ];
    }
}
