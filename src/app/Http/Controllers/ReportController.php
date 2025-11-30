<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\ReportNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ReportController extends Controller
{
    public function store(Request $request)
    {
        // Проверяем, что запрос ожидает JSON
        if (!$request->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid request format'
            ], 400);
        }

        // Валидация данных
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:5|max:2000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
        ], [
            'message.required' => 'Пожалуйста, опишите вашу проблему',
            'message.min' => 'Сообщение должно содержать не менее 5 символов',
            'message.max' => 'Сообщение не должно превышать 2000 символов',
            'image.image' => 'Файл должен быть изображением',
            'image.mimes' => 'Поддерживаются только JPEG, PNG, JPG и GIF форматы',
            'image.max' => 'Размер изображения не должен превышать 5MB',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first()
            ], 422);
        }

        try {
            $imagePath = null;

            // Обработка изображения, если оно есть
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                // Сохраняем в PUBLIC директорию
                $imagePath = $image->storeAs('reports', $fileName, 'public');

                \Log::info('Image saved to public storage', [
                    'path' => $imagePath,
                    'full_url' => Storage::disk('public')->url($imagePath)
                ]);
            }

            $user = auth()->user();
            // Отправляем уведомление админам
            $admins = User::getAdmins();
            foreach ($admins as $admin) {
                $admin->notify(new ReportNotification($user, $request->message, $imagePath));
            }

            return response()->json([
                'success' => true,
                'message' => 'Ваше сообщение успешно отправлено в техподдержку',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Error saving report: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'ip' => $request->ip()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }

}
