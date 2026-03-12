<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheableTrait
{
    protected function remember(string $key, $ttl, callable $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    protected function forget(string $key): void
    {
        Cache::forget($key);
    }
}
