<?php
namespace App\Http\Controllers;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\UserActivity;
use App\Services\ChatService;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OllamaController extends Controller {
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
        try {
            $prompt = $request->input('prompt');
            $model = $request->input('model');
            $chatId = $request->input('chatID');
            $userId = auth()->id();

            // Создаем сервис
            $chatService = new ChatService($chatId, $prompt, $model, $userId);

            // Получаем ответ
            $result = $chatService->getResponse();

            return response()->json([
                'response' => $result['response'],
                'chat_id' => $result['chat_id'],
                'is_new_chat' => $result['is_new_chat'],
                'chat_name' => $result['chat_name']
            ]);

        } catch (\Exception $e) {
            Log::error('Chat service error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Ошибка при обработке запроса',
                'chat_id' => $chatId // Возвращаем исходный chat_id при ошибке
            ], 500);
        }
    }
}
