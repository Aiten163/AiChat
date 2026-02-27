<?php

namespace App\Services;

class ChatTitleService
{
    public function generateTitle(string $firstMessage): string
    {
        $cleanMessage = trim($firstMessage);

        if (empty($cleanMessage)) {
            return 'Новый чат';
        }

        $cleanMessage = preg_replace('/\s+/', ' ', $cleanMessage);

        $words = explode(' ', $cleanMessage);
        $wordCount = count($words);

        $takeWords = min($wordCount, 6);
        if ($takeWords > 4) {
            $sample = implode(' ', array_slice($words, 0, $takeWords));
            if (strlen($sample) > 40) {
                $takeWords = 4;
            }
        }

        $firstWords = array_slice($words, 0, $takeWords);
        $title = implode(' ', $firstWords);

        if ($wordCount > $takeWords) {
            $title .= '...';
        }

        if (strlen($title) > 50) {
            $title = substr($title, 0, 47) . '...';
        }

        return $title;
    }
}
