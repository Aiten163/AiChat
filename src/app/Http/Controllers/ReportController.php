<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReportRequest;
use App\Services\Report\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    public function store(ReportRequest $request): JsonResponse
    {
        try {
            $this->reportService->sendReport(
                user: auth()->user(),
                message: $request->input('message'),
                image: $request->file('image')
            );

            return response()->json([
                'success' => true,
                'message' => 'Ваше сообщение успешно отправлено в техподдержку',
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Report sending failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Произошла ошибка при отправке сообщения'
            ], 500);
        }
    }
}
