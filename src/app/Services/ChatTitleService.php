<?php

namespace App\Services;

class ChatTitleService
{
    /**
     * Генерирует название для чата на основе первого сообщения
     */
    public function generateTitle(string $firstMessage): string
    {
        $cleanMessage = trim($firstMessage);

        // Если сообщение пустое
        if (empty($cleanMessage)) {
            return 'Новый чат';
        }

        // Удаляем лишние пробелы
        $cleanMessage = preg_replace('/\s+/', ' ', $cleanMessage);

        // Берем первые 4-6 слов
        $words = explode(' ', $cleanMessage);
        $wordCount = count($words);

        // Определяем сколько слов взять (4-6 в зависимости от длины)
        $takeWords = min($wordCount, 6);
        if ($takeWords > 4) {
            // Проверяем длину, если слишком длинные слова - берем меньше
            $sample = implode(' ', array_slice($words, 0, $takeWords));
            if (strlen($sample) > 40) {
                $takeWords = 4;
            }
        }

        $firstWords = array_slice($words, 0, $takeWords);
        $title = implode(' ', $firstWords);

        // Добавляем многоточие если взяли не все слова
        if ($wordCount > $takeWords) {
            $title .= '...';
        }

        // Обрезаем до максимальной длины если нужно
        if (strlen($title) > 50) {
            $title = substr($title, 0, 47) . '...';
        }

        return $title;
    }
}
