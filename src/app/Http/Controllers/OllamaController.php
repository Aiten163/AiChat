<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\UserActivity;
use App\Notifications\ProhibitedMessageGet;
use App\Services\ChatService;
use App\Services\FilterServices\FilterService;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OllamaController extends Controller
{
    private ?int $user_id = null;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user_id = Auth::id();
            return $next($request);
        });
    }

    public function postRequest(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required|string',
            'chatID' => 'required|min:1',
            'model' => 'required|string|max:100',
        ]);
        $prompt = $request->input('prompt');
        $model = $request->input('model');
        $chatId = $request->input('chatID');
        $userId = $this->user_id;

        try {
            $filterService = new FilterService($model);
            if(!$filterService->filter($prompt, $model)) {
                throw new \Exception('Данный запрос нарушает правила информационной безопасности');
            }

            $chatService = new ChatService($chatId, $prompt, $model, $userId);

            return $chatService->processMessage();

        } catch (\Exception $e) {
            Log::error('Chat service error: ' . $e->getMessage());

            $admins = User::getAdmins();
            Notification::send($admins, new ProhibitedMessageGet($userId, $prompt, $e->getMessage()));
            //TODO:: добавить отправку писем для пользователя Notification::send($this->user_id, new ProhibitedMessageGet($userId, $prompt, $e->getMessage()));
            return response()->stream(function () use ($e, $chatId) {
                echo "data: " . json_encode([
                        'type' => 'error',
                        'error' => $e->getMessage(),
                        'chat_id' => $chatId ?? null
                    ]) . "\n\n";
                flush();
            }, 500, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'X-Accel-Buffering' => 'no',
            ]);
        }
    }
}
