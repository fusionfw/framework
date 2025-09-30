<?php

namespace Fusion\Core\Cache;

/**
 * Array Cache Driver (for testing)
 */
class ArrayCache implements CacheInterface
{
    private $cache = [];

    /**
     * Get value from cache
     */
    public function get(string $key, $default = null)
    {
        if (!isset($this->cache[$key])) {
            return $default;
        }

        $data = $this->cache[$key];

        if ($data['expires'] > 0 && $data['expires'] < time()) {
            unset($this->cache[$key]);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set value in cache
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $expires = $ttl ? time() + $ttl : 0;

        $this->cache[$key] = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];

        return true;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        if (!isset($this->cache[$key])) {
            return false;
        }

        $data = $this->cache[$key];

        if ($data['expires'] > 0 && $data['expires'] < time()) {
            unset($this->cache[$key]);
            return false;
        }

        return true;
    }

    /**
     * Remove value from cache
     */
    public function forget(string $key): bool
    {
        unset($this->cache[$key]);
        return true;
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        $this->cache = [];
        return true;
    }
}
