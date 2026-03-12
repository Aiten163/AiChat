<?php

namespace App\Http\Controllers;

use App\Http\Requests\OllamaRequest;
use App\Services\Chat\ChatService;
use App\Services\Filter\MessageFilterService;
use App\Services\Notification\AdminNotificationService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class OllamaController extends Controller
{
    private ?int $userId = null;

    public function __construct(
        private readonly MessageFilterService $filterService,
        private readonly ChatService $chatService,
        private readonly AdminNotificationService $notificationService
    ) {
        $this->middleware(function ($request, $next) {
            $this->userId = auth()->id();
            return $next($request);
        });
    }

    public function postRequest(OllamaRequest $request): Response
    {
        $prompt = $request->input('prompt');
        $model = $request->input('model');
        $chatId = $request->input('chatID');

        try {
            if (!$this->filterService->validateMessage($prompt, $model)) {
                throw new \Exception('Данный запрос нарушает правила информационной безопасности');
            }

            return $this->chatService->processMessage(
                chatId: $chatId,
                message: $prompt,
                modelName: $model,
                userId: $this->userId
            );

        } catch (\Exception $e) {
            Log::error('Chat processing error', [
                'user_id' => $this->userId,
                'error' => $e->getMessage()
            ]);

            $this->notificationService->notifyAdminsAboutViolation(
                userId: $this->userId,
                message: $prompt,
                error: $e->getMessage()
            );

            return $this->createErrorResponse($e, $chatId);
        }
    }

    private function createErrorResponse(\Exception $e, ?string $chatId): Response
    {
        return response()->stream(function () use ($e, $chatId) {
            echo "data: " . json_encode([
                    'type' => 'error',
                    'error' => $e->getMessage(),
                    'chat_id' => $chatId
                ]) . "\n\n";
            flush();
        }, 500, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
