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
            $user = auth()->user();
            $imagePath = null;

            // Обработка изображения, если оно есть
            if ($request->hasFile('image') && $request->file('image')->isValid()) {
                $image = $request->file('image');
                $fileName = Str::uuid() . '.' . $image->getClientOriginalExtension();
                $imagePath = $image->storeAs('reports', $fileName);

                \Log::info('Image saved', ['path' => $imagePath]);
            }

            // Отправляем уведомление админам
            $admins = User::getAdmins();

            \Log::info('Found admins', ['count' => $admins->count(), 'admins' => $admins->pluck('email')]);

            foreach ($admins as $admin) {
                \Log::info('Sending notification to admin', ['admin_id' => $admin->id, 'email' => $admin->email]);
                $admin->notify(new ReportNotification($user, $request->message, $imagePath));
            }

            \Log::info('Notification sent successfully');

            return response()->json([
                'success' => true,
                'message' => 'Ваше сообщение успешно отправлено в техподдержку',
            ], 201);

        }catch (\Exception $e) {
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

    /**
     * Метод для защищенного доступа к изображениям
     */
    public function showImage($filename)
    {
        // Проверяем авторизацию
        if (!auth()->check()) {
            abort(403, 'Доступ запрещен');
        }

        // Дополнительная проверка - только админы
        $user = auth()->user();
        if (!$user->is_admin) { // Убедитесь, что у пользователя есть свойство is_admin
            abort(403, 'У вас нет прав для просмотра изображений техподдержки');
        }

        $path = storage_path('app/reports/' . $filename);

        if (!file_exists($path)) {
            abort(404, 'Изображение не найдено');
        }

        // Определяем MIME-тип
        $mime = mime_content_type($path);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mime, $allowedMimes)) {
            abort(403, 'Недопустимый тип файла');
        }

        return response()->file($path, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600' // Кэшируем на 1 час
        ]);
    }
}
