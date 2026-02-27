<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function rename(Chat $chat, Request $request)
    {
        if ($chat->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $request->validate(['name' => 'required|string|max:100']);

        $chat->update(['name' => $request->name]);

        return response()->json([
            'success' => true,
            'new_name' => $chat->name
        ]);
    }

    public function destroy(Chat $chat)
    {
        if ($chat->user_id !== auth()->id()) {
            return response()->json(['error' => 'Доступ запрещен'], 403);
        }

        $updated = $chat->update(['show' => false]);

        if ($updated) {
            return response()->json(['success' => true, 'message' => 'Чат удален']);
        } else {
            return response()->json(['error' => 'Ошибка при обновлении'], 500);
        }
    }
}
