<?php

namespace App\Services\Neural;

use App\Models\Neural;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

class NeuralCacheService
{
    private const NEURALS_LIST_KEY = 'neurals:list';
    private const NEURAL_DETAIL_KEY = 'neural:';
    private const CACHE_TTL = 3600; // 1 час

    /**
     * Получить список всех нейросетей
     */
    public function getAllNeurals(): Collection
    {
        return Cache::tags(['neurals'])->remember(self::NEURALS_LIST_KEY, self::CACHE_TTL, function () {
            return Neural::get(['show_name', 'name', 'id', 'temperature', 'countLastMessage']);
        });
    }

    /**
     * Получить нейросеть по имени с деталями
     */
    public function getNeuralByName(string $name): ?Neural
    {
        $cacheKey = self::NEURAL_DETAIL_KEY . $name;

        return Cache::tags(['neurals', 'neural_details'])->remember($cacheKey, self::CACHE_TTL, function () use ($name) {
            return Neural::with(['basePrompt', 'neuralFilter'])
                ->where('name', $name)
                ->first();
        });
    }

    /**
     * Получить нейросеть по ID
     */
    public function getNeuralById(int $id): ?Neural
    {
        $cacheKey = self::NEURAL_DETAIL_KEY . 'id:' . $id;

        return Cache::tags(['neurals', 'neural_details'])->remember($cacheKey, self::CACHE_TTL, function () use ($id) {
            return Neural::with(['basePrompt', 'neuralFilter'])->find($id);
        });
    }

    /**
     * Очистить кэш для конкретной нейросети
     */
    public function clearNeuralCache(string|int $identifier): void
    {
        if (is_numeric($identifier)) {
            $neural = Neural::find($identifier);
            $name = $neural?->name;
        } else {
            $name = $identifier;
        }

        if ($name) {
            Cache::tags(['neurals'])->forget(self::NEURAL_DETAIL_KEY . $name);
        }

        Cache::tags(['neurals'])->forget(self::NEURALS_LIST_KEY);

        Log::info('Neural cache cleared', ['identifier' => $identifier]);
    }

    /**
     * Очистить весь кэш нейросетей
     */
    public function clearAllNeuralCache(): void
    {
        Cache::tags(['neurals', 'neural_details'])->flush();
        Log::info('All neural cache cleared');
    }

    /**
     * Получить популярные нейросети (для статистики)
     */
    public function getPopularNeurals(int $limit = 5): Collection
    {
        return Cache::tags(['neurals'])->remember('neurals:popular:' . $limit, self::CACHE_TTL, function () use ($limit) {
            return Neural::where('show_name', true)
                ->limit($limit)
                ->get(['id', 'name', 'show_name']);
        });
    }
}
