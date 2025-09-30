<?php

namespace Fusion\Core\Cache;

/**
 * Cache Interface
 */
interface CacheInterface
{
    /**
     * Get value from cache
     */
    public function get(string $key, $default = null);

    /**
     * Set value in cache
     */
    public function set(string $key, $value, int $ttl = null): bool;

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool;

    /**
     * Remove value from cache
     */
    public function forget(string $key): bool;

    /**
     * Clear all cache
     */
    public function flush(): bool;
}
