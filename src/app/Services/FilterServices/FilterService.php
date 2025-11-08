<?php

namespace App\Services\FilterServices;

use App\Models\NeuralFilter;
use Illuminate\Support\Facades\Log;

class FilterService
{
    private NeuralFilter $neuralFilter;
    public function __construct($neural)
    {
        $this->neuralFilter = NeuralFilter::where('activeSimple', '=', 1)->orWhere('activePrompt', '=', 1)->first();
    }

    public function choiceFilter($filter, $text): bool
    {
        switch ($filter) {
            case 'simple':
            {
                $result = SimpleFilterService::filter();
                break;
            }
            case 'neural':
            {
                $result = NeuralFilterService::filter();
                break;
            }
            default:
            {
                $result = false;
                Log::error('Undefined filter: ' . $filter);
                break;
            }
        }
        return $result;
    }
}
