<?php

namespace App\Services\FilterServices;

use Cloudstudio\Ollama\Ollama;
use Illuminate\Support\Facades\Log;

class NeuralFilterService
{
    public static function filter(string $message, string $filterPrompt, string $modelName): bool
    {
        try {

            $systemPrompt = "Ты - система фильтрации контента. Проанализируй сообщение и определи, содержит ли оно следующие запретные темы: " . $filterPrompt . "\n\nОтветь ТОЛЬКО 'true' если сообщение безопасно и не содержит запретных тем, или 'false' если сообщение содержит запретные темы. Не объясняй свой ответ.";

            $fullPrompt = $systemPrompt . "\n\nСообщение: " . $message;


            $response = app(Ollama::class)
                ->prompt($fullPrompt)
                ->model($modelName)
                ->options([
                    'temperature' => 0.1,
                    'num_predict' => 50 // Увеличиваем лимит символов
                ])
                ->ask();


            // ⚡ ИСПРАВЛЕНИЕ: Правильно извлекаем контент
            $responseContent = $response['message']['content'] ?? $response['response'] ?? '';


            return self::parseResponse($responseContent);

        } catch (\Exception $e) {
            return true; // При ошибке пропускаем сообщение
        }
    }

    private static function parseResponse(string $response): bool
    {
        $response = strtolower(trim($response));


        // Если ответ пустой - считаем безопасным
        if (empty($response)) {
            return true;
        }

        // Ищем явные признаки блокировки
        if (str_contains($response, 'false') ||
            str_contains($response, 'block') ||
            str_contains($response, 'reject') ||
            str_contains($response, 'danger') ||
            str_contains($response, 'опасно') ||
            str_contains($response, 'заблокировать')) {
            return false;
        }

        // Ищем явные признаки разрешения
        if (str_contains($response, 'true') ||
            str_contains($response, 'safe') ||
            str_contains($response, 'allow') ||
            str_contains($response, 'разрешено') ||
            str_contains($response, 'безопасно')) {
            return true;
        }

        // Короткие варианты
        if ($response === 'f' || $response === '0' || $response === 'no' || $response === 'н') {
            return false;
        }

        if ($response === 't' || $response === '1' || $response === 'yes' || $response === 'д') {
            return true;
        }

        return true;
    }
}
