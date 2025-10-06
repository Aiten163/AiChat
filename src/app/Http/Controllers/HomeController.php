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
        $chats = Chat::where('user_id', Auth::id())->get();
        $neurals = Neural::all()->only(['show_name','name']);
        return view('home', ['chats' => $chats, 'neurals' => $neurals]);
    }
    public function getHistoryChat(Request $request) {

        Log::info($request->chat_id);
        return ChatMessage::where('chat_id', $request->chat_id)->get()->only(['message', 'role']);
    }
}
