<?php

namespace App\Services\FilterServices;

use App\Models\NeuralFilter;
use Illuminate\Support\Facades\Log;

class FilterService
{
    private NeuralFilter | null  $neuralFilter = null;

    public function __construct($neural)
    {
        $this->neuralFilter = NeuralFilter::where('activeSimple', '=', 1)
            ->orWhere('activePrompt', '=', 1)
            ->with('neural')
            ->first();
    }

    public function filter($text): bool
    {
        if ($this->neuralFilter === null) {
            return true;
        }
        if ($this->neuralFilter->activeSimple) {
            if (!SimpleFilterService::filter($text, $this->neuralFilter->simpleFilter)) {
                return false;
            };
        }
        if ($this->neuralFilter->activePrompt) {
            if (!NeuralFilterService::filter($text, $this->neuralFilter->simpleFilter, $this->neuralFilter->neural->name)) {
                return false;
            }
        }
        return true;
    }
}
