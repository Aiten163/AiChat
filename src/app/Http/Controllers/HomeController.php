<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Neural;
use http\Message;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    public function index() {
        $chats = Chat::where(['user_id' => auth()->id(), 'show' => true])
            ->orderBy('id', 'desc')
            ->get(['id', 'name','lastMessage']);

        $neurals = Neural::get(['show_name', 'name']);

        return view('home', ['chats' => $chats, 'neurals' => $neurals]);
    }
    public function getHistoryChat(Request $request) {
        $request->validate([
            'chat_id' => 'required|integer'
        ]);

        $chat = Chat::where(['id' => $request->chat_id, "show" => true, 'user_id' => auth()->id()])
            ->firstOrFail();

        $messages = ChatMessage::where('chat_id', $request->chat_id)
            ->select('message', 'role')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }
}
