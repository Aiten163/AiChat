<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRenameRequest;
use App\Models\Chat;
use App\Services\Chat\ChatManagementService;
use Illuminate\Http\JsonResponse;

class ChatController extends Controller
{
    public function __construct(
        private readonly ChatManagementService $chatService
    ) {}

    public function rename(Chat $chat, ChatRenameRequest $request): JsonResponse
    {
        try {
            $newName = $this->chatService->rename($chat, $request->input('name'));

            return response()->json([
                'success' => true,
                'new_name' => $newName
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при переименовании чата'
            ], 500);
        }
    }

    public function destroy(Chat $chat): JsonResponse
    {
        try {
            $this->chatService->softDelete($chat);

            return response()->json([
                'success' => true,
                'message' => 'Чат удален'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Ошибка при удалении чата'
            ], 500);
        }
    }
}
