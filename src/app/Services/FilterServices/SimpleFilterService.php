<?php

namespace App\Services\FilterServices;


use App\Models\NeuralFilter;
use Illuminate\Support\Str;

class SimpleFilterService
{
    public static function filter($text, $textWithWords)
    {
        $wordList = array_map('trim', explode(',', $textWithWords));
        if (Str::contains($text, $wordList)) {
            return false;
        }
        return true;
    }
}
