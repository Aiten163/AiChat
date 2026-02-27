<?php

namespace App\Services\FilterServices;

use App\Models\NeuralFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FilterService
{
    private NeuralFilter | null  $neuralFilter = null;
    private ?string $reason;

    public function __construct($neural)
    {
        if (Cache::has('neuralFilter')) {
            $this->neuralFilter = Cache::get('neuralFilter');
        } else {
            $this->neuralFilter = NeuralFilter::where('activeSimple', '=', 1)
                ->orWhere('activePrompt', '=', 1)
                ->with('neural')
                ->first();
            Cache::set('neuralFilter', $this->neuralFilter, 3600);
        }
    }

    public function filter($text): bool
    {
        Log::info($this->neuralFilter);
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
