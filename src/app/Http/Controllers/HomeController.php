<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatHistoryRequest;
use App\Services\Chat\ChatHistoryService;
use App\Services\Neural\NeuralCacheService;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct(
        private readonly ChatHistoryService $chatHistoryService,
        private readonly NeuralCacheService $neuralCacheService
    ) {
        $this->middleware('auth')->except(['index']);
    }

    public function index(?int $chatId = null): View
    {
        $chats = collect();
        $userId = Auth::id();

        if ($userId) {
            $chats = $this->chatHistoryService->getUserChats($userId);
        }

        $neurals = $this->neuralCacheService->getAllNeurals();

        return view('home', [
            'chats' => $chats,
            'neurals' => $neurals,
            'currentChatId' => $chatId
        ]);
    }

    public function getHistoryChat(ChatHistoryRequest $request): JsonResponse
    {
        $messages = $this->chatHistoryService->getChatMessages(
            $request->input('chat_id'),
            auth()->id()
        );

        return response()->json($messages);
    }
}
