<?php

namespace App\Services\Filter;

use Cloudstudio\Ollama\Ollama;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NeuralFilterService
{
    /**
     * Фильтрация сообщения с помощью нейросети
     */
    public static function filter(string $message, string $filterPrompt, string $modelName): bool
    {
        // Метрики для отслеживания
        $startTime = microtime(true);

        try {
            $systemPrompt = "Ты - система фильтрации контента. Проанализируй сообщение и определи, содержит ли оно следующие запретные темы: " . $filterPrompt . "\n\n";
            $systemPrompt .= "Ответь ТОЛЬКО 'true' если сообщение безопасно и не содержит запретных тем, или 'false' если сообщение содержит запретные темы. Не объясняй свой ответ, не добавляй никакого текста кроме 'true' или 'false'.";

            $fullPrompt = $systemPrompt . "\n\nСообщение для анализа: \"" . $message . "\"";

            Log::debug('Neural filter request', [
                'model' => $modelName,
                'prompt_length' => strlen($fullPrompt),
                'message_length' => strlen($message)
            ]);

            $response = app(Ollama::class)
                ->prompt($fullPrompt)
                ->model($modelName)
                ->options([
                    'temperature' => 0.1,
                    'num_predict' => 50
                ])
                ->ask();

            $responseContent = $response['message']['content'] ?? $response['response'] ?? '';

            $result = self::parseResponse($responseContent);

            $executionTime = round((microtime(true) - $startTime) * 1000, 2); // в миллисекундах

            Log::info('Neural filter completed', [
                'model' => $modelName,
                'result' => $result,
                'execution_time_ms' => $executionTime,
                'response_sample' => Str::limit($responseContent, 50)
            ]);

            return $result;

        } catch (\Exception $e) {
            Log::error('Neural filter error', [
                'error' => $e->getMessage(),
                'model' => $modelName,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000, 2)
            ]);

            return true;
        }
    }

    /**
     * Парсинг ответа нейросети
     */
    private static function parseResponse(string $response): bool
    {
        $response = strtolower(trim($response));
        $response = preg_replace('/[^\w\s]/u', '', $response); // Удаляем пунктуацию

        if (empty($response)) {
            return true;
        }

        $blockPatterns = [
            'false', 'block', 'reject', 'danger',
            'опасно', 'заблокировать', 'запрещено',
            'нарушает', 'содержит', 'запретные',
            'f', '0', 'no', 'н', 'нет'
        ];

        $allowPatterns = [
            'true', 'safe', 'allow', 'ok',
            'разрешено', 'безопасно', 'допустимо',
            't', '1', 'yes', 'д', 'да'
        ];

        foreach ($blockPatterns as $pattern) {
            if (str_contains($response, $pattern)) {
                return false;
            }
        }

        foreach ($allowPatterns as $pattern) {
            if (str_contains($response, $pattern)) {
                return true;
            }
        }

        return true;
    }
}
