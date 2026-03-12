<?php

namespace App\Services\Filter;

use Illuminate\Support\Str;

class SimpleFilterService
{
    public static function filter(string $text, string $keywords): bool
    {
        $wordList = array_map(function($word) {
            return trim($word);
        }, explode(',', $keywords));

        $wordList = array_filter($wordList, function($word) {
            return !empty($word);
        });

        if (empty($wordList)) {
            return true;
        }

        $lowerText = Str::lower($text);

        foreach ($wordList as $word) {
            if (empty($word)) {
                continue;
            }

            if (Str::contains($lowerText, Str::lower($word))) {
                Log::debug('Simple filter blocked', [
                    'word' => $word,
                    'text_sample' => Str::limit($text, 100)
                ]);
                return false;
            }
        }

        return true;
    }
}
