<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function store(Request $request)
    {
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

            $this->reportService->sendReport(
                $user,
                $request->message,
                $request->file('image')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ваше сообщение успешно отправлено в техподдержку',
            ]);

        } catch (\Exception $e) {
            \Log::error('Report sending failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при отправке сообщения. Пожалуйста, попробуйте позже.'
            ], 500);
        }
    }
}
