<?php

namespace Fusion\Cache;

use Fusion\Config;
use Fusion\Logger;

/**
 * Cache Manager
 */
class CacheManager
{
    private $config;
    private $logger;
    private $drivers = [];

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get cache driver
     */
    public function driver(string $driver = null): CacheInterface
    {
        $driver = $driver ?: $this->config->get('cache.default', 'file');

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * Create cache driver
     */
    private function createDriver(string $driver): CacheInterface
    {
        switch ($driver) {
            case 'file':
                return new FileCache($this->config, $this->logger);
            case 'redis':
                return new RedisCache($this->config, $this->logger);
            case 'array':
                return new ArrayCache();
            default:
                throw new \Exception("Cache driver '{$driver}' not supported");
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key, $default = null)
    {
        return $this->driver()->get($key, $default);
    }

    /**
     * Set value in cache
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        return $this->driver()->set($key, $value, $ttl);
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        return $this->driver()->has($key);
    }

    /**
     * Remove value from cache
     */
    public function forget(string $key): bool
    {
        return $this->driver()->forget($key);
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        return $this->driver()->flush();
    }

    /**
     * Remember value (get or set)
     */
    public function remember(string $key, callable $callback, int $ttl = null)
    {
        if ($this->has($key)) {
            return $this->get($key);
        }

        $value = $callback();
        $this->set($key, $value, $ttl);
        return $value;
    }

    /**
     * Remember value forever
     */
    public function rememberForever(string $key, callable $callback)
    {
        return $this->remember($key, $callback, 0);
    }
}
