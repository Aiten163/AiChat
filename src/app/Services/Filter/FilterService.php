<?php

namespace App\Services\Filter;

use App\Models\NeuralFilter;
use Illuminate\Support\Facades\Log;

class FilterService
{
    private ?NeuralFilter $neuralFilter = null;
    private ?string $reason;
    private string $neuralName;
    private FilterCacheService $cacheService;

    public function __construct(string $neuralName)
    {
        $this->neuralName = $neuralName;
        $this->cacheService = app(FilterCacheService::class);
        $this->loadFilterForNeural();
    }

    /**
     * Загружает фильтр для конкретной нейросети (с использованием Redis)
     */
    private function loadFilterForNeural(): void
    {
        $this->neuralFilter = $this->cacheService->getFilterForNeural($this->neuralName);
    }

    /**
     * Основной метод фильтрации сообщения
     */
    public function filter(string $text): bool
    {
        Log::debug('Filter check for neural: ' . $this->neuralName, [
            'has_filter' => $this->neuralFilter !== null
        ]);

        if ($this->neuralFilter === null) {
            return true;
        }

        if ($this->neuralFilter->activePrompt && !empty($this->neuralFilter->prompt)) {
            $cachedResult = $this->cacheService->getCachedFilterResult(
                $text,
                $this->neuralFilter->prompt,
                $this->neuralName
            );

            if ($cachedResult !== null) {
                Log::debug('Using cached filter result', ['result' => $cachedResult]);
                if (!$cachedResult) {
                    $this->reason = 'neural_filter_cached';
                }
                return $cachedResult;
            }
        }

        if ($this->neuralFilter->activeSimple && !empty($this->neuralFilter->simpleFilter)) {
            if (!SimpleFilterService::filter($text, $this->neuralFilter->simpleFilter)) {
                $this->reason = 'simple_filter';
                Log::info('Message blocked by simple filter', [
                    'neural' => $this->neuralName,
                    'filter' => $this->neuralFilter->simpleFilter
                ]);
                return false;
            }
        }

        if ($this->neuralFilter->activePrompt && !empty($this->neuralFilter->prompt)) {
            $result = NeuralFilterService::filter($text, $this->neuralFilter->prompt, $this->neuralName);

            $this->cacheService->cacheFilterResult(
                $text,
                $this->neuralFilter->prompt,
                $this->neuralName,
                $result
            );

            if (!$result) {
                $this->reason = 'neural_filter';
                Log::info('Message blocked by neural filter', [
                    'neural' => $this->neuralName,
                    'filter' => $this->neuralFilter->prompt
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Получить причину блокировки
     */
    public function getReason(): ?string
    {
        return $this->reason;
    }
}
