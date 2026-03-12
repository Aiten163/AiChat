<?php

namespace App\Services\Filter;

use App\Models\NeuralFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class FilterCacheService
{
    private const FILTER_CACHE_KEY = 'neural_filter';
    private const FILTER_CACHE_TTL = 3600;
    private const FILTER_RESULTS_TTL = 300;

    /**
     * Получить фильтр для нейросети с кэшированием
     */
    public function getFilterForNeural(string $neuralName): ?NeuralFilter
    {
        $cacheKey = self::FILTER_CACHE_KEY . ':' . $neuralName;

        return Cache::tags(['neural_filters'])->remember($cacheKey, self::FILTER_CACHE_TTL, function () use ($neuralName) {
            Log::debug('Loading filter from database for neural: ' . $neuralName);

            return NeuralFilter::whereHas('neural', function($query) use ($neuralName) {
                $query->where('name', $neuralName);
            })
                ->where(function($query) {
                    $query->where('activeSimple', true)
                        ->orWhere('activePrompt', true);
                })
                ->with('neural')
                ->first();
        });
    }

    /**
     * Получить все активные фильтры
     */
    public function getAllActiveFilters(): \Illuminate\Database\Eloquent\Collection
    {
        return Cache::tags(['neural_filters'])->remember('all_active_filters', self::FILTER_CACHE_TTL, function () {
            return NeuralFilter::where('activeSimple', true)
                ->orWhere('activePrompt', true)
                ->with('neural')
                ->get();
        });
    }

    /**
     * Сохранить результат фильтрации в кэш
     */
    public function cacheFilterResult(string $message, string $filterPrompt, string $modelName, bool $result): void
    {
        $cacheKey = $this->generateResultCacheKey($message, $filterPrompt, $modelName);

        Cache::tags(['filter_results'])->put($cacheKey, $result, self::FILTER_RESULTS_TTL);
    }

    /**
     * Получить результат фильтрации из кэша
     */
    public function getCachedFilterResult(string $message, string $filterPrompt, string $modelName): ?bool
    {
        $cacheKey = $this->generateResultCacheKey($message, $filterPrompt, $modelName);

        return Cache::tags(['filter_results'])->get($cacheKey);
    }

    /**
     * Очистить кэш фильтра для нейросети
     */
    public function clearFilterCache(string $neuralName): void
    {
        Cache::tags(['neural_filters'])->forget(self::FILTER_CACHE_KEY . ':' . $neuralName);
        Log::info('Filter cache cleared for neural: ' . $neuralName);
    }

    /**
     * Очистить все кэши фильтров
     */
    public function clearAllFiltersCache(): void
    {
        Cache::tags(['neural_filters', 'filter_results'])->flush();
        Log::info('All filters cache cleared');
    }

    /**
     * Очистить результаты фильтрации для сообщения
     */
    public function clearFilterResult(string $message, string $filterPrompt, string $modelName): void
    {
        $cacheKey = $this->generateResultCacheKey($message, $filterPrompt, $modelName);
        Cache::tags(['filter_results'])->forget($cacheKey);
    }

    /**
     * Сгенерировать ключ для кэширования результата
     */
    private function generateResultCacheKey(string $message, string $filterPrompt, string $modelName): string
    {
        return 'filter_result:' . md5($message . $filterPrompt . $modelName);
    }
}
