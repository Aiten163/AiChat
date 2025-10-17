<?php
namespace App\Http\Controllers;
use App\Models\Chat;
use App\Models\ChatMessage;
use App\Models\User;
use App\Models\UserActivity;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OllamaController extends Controller {
    private int $user_id;

    public function __construct()
    {
         $this->user_id=Auth::id();
    }

    public function postRequest(Request $request)
    {
        $request->validate([
            'chatId' => 'integer|nullable',
            'prompt' => 'required|string|min:1|max:15000',
            'model' => 'required|string|min:1|max:50',
        ]);
        # TODO: Разбить контроллер на сервис и добавить валидатор, допилить код с логикой чатов и на фронте сохранять нужные параметры для этого контроллера
        $request->chatId;
        $request->prompt;
        $request->model;


        if(!$request->chatId) {
            Chat::create([
                'user_id' => $this->user_id,
            ]);
        } else {

        }
        ChatMessage::create([
            'chat_id' => 1,
            'message' => $request->prompt,
            'role' => 'user'
        ]);
        $lastMessages = Chat::find(1)->getLastMessages(5);
        $conversation = $lastMessages->map(function ($message) {
            return [
                'role' => $message->role,
                'content' => $message->message
            ];
        })->toArray();


        $response = Ollama::model('llama3:8b')
            ->chat($conversation);

        ChatMessage::create([
            'chat_id' => 1,
            'message' => $response['message']['content'],
            'role' => $response['message']['role']
        ]);

        UserActivity::updateActivity(Auth::id());

        return response()->json([
            'response' => $response['message']['content']
        ]);
    }

}
