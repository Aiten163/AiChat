<?php

namespace App\Services\Neural;

use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Support\Facades\Log;

class OllamaService
{
    public function streamChat(string $model, array $conversation, int $temperature, callable $onContent): void
    {
        $response = Ollama::model($model)
            ->options(['temperature' => $temperature])
            ->stream(true)
            ->chat($conversation);

        Ollama::processStream($response->getBody(), function($data) use ($onContent) {
            if (isset($data['message']['content'])) {
                $onContent($data['message']['content']);
                return !connection_aborted();
            }
            return true;
        });
    }

    public function chat(string $model, array $conversation, int $temperature): array
    {
        $response = Ollama::model($model)
            ->options(['temperature' => $temperature])
            ->chat($conversation);

        return $response->json();
    }
}
