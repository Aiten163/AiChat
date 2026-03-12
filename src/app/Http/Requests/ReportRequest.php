<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'message' => 'required|string|min:5|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Пожалуйста, опишите вашу проблему',
            'message.min' => 'Сообщение должно содержать не менее 5 символов',
            'message.max' => 'Сообщение не должно превышать 2000 символов',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Поддерживаются только JPEG, PNG, JPG и GIF форматы',
            'image.max' => 'Размер изображения не должен превышать 5MB',
        ];
    }
}
