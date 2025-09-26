<?php
namespace App\Http\Controllers;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;

class OllamaController extends Controller {

    public function postRequest(Request $request)
    {
        $response = Ollama::agent('You are a helpful assistant.')
            ->prompt($request->prompt)
            ->model('llama3:8b')
            ->ask();

        return response()->json([
            'response' => $response['response'],
            'status' => 'success'
        ]);
    }

}
