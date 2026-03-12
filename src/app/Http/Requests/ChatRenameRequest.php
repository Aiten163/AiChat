<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRenameRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->chat->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|min:1',
        ];
    }
}
