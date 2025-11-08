<?php

namespace App\Services\FilterServices;


use Illuminate\Support\Str;

class SimpleFilterService
{
    public static function filter($text, $neural)
    {
        $textWithWords = self::getWords($neural);
        $wordList = array_map('trim', explode(',', $textWithWords));
        if (Str::contains($text, $wordList)) {

        }

    }
    private static function getWords($neural)
    {

    }
}
