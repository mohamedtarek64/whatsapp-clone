<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait Cacheable
{
    /**
     * Get cached data or retrieve from callable
     */
    protected function cached(string $key, $ttl = 3600, callable $callback = null)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Get or set cache for a value
     */
    protected function getCacheOrStore(string $key, $value, $ttl = 3600)
    {
        return Cache::remember($key, $ttl, function () use ($value) {
            return $value;
        });
    }

    /**
     * Forget cache by key
     */
    protected function forgetCache(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Forget multiple cache keys
     */
    protected function forgetCacheMany(array $keys): void
    {
        Cache::forget($keys);
    }

    /**
     * Clear all cache for this model
     */
    protected function clearModelCache(): void
    {
        $modelName = class_basename($this);
        Cache::tags([$modelName])->flush();
    }
}
