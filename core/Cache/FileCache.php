<?php

namespace Fusion\Core\Cache;

use Fusion\Core\Config;
use Fusion\Core\Logger;

/**
 * File Cache Driver
 */
class FileCache implements CacheInterface
{
    private $config;
    private $logger;
    private $path;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
        $this->path = $this->config->get('app.cache_path', dirname(__DIR__, 2) . '/storage/cache');

        if (!is_dir($this->path)) {
            mkdir($this->path, 0755, true);
        }
    }

    /**
     * Get value from cache
     */
    public function get(string $key, $default = null)
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return $default;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] > 0 && $data['expires'] < time()) {
            $this->forget($key);
            return $default;
        }

        return $data['value'];
    }

    /**
     * Set value in cache
     */
    public function set(string $key, $value, int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $expires = $ttl ? time() + $ttl : 0;

        $data = [
            'value' => $value,
            'expires' => $expires,
            'created' => time()
        ];

        $result = file_put_contents($file, serialize($data), LOCK_EX);

        if ($result === false) {
            $this->logger->error('Failed to write cache file', ['key' => $key, 'file' => $file]);
            return false;
        }

        return true;
    }

    /**
     * Check if key exists in cache
     */
    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return false;
        }

        $data = unserialize(file_get_contents($file));

        if ($data['expires'] > 0 && $data['expires'] < time()) {
            $this->forget($key);
            return false;
        }

        return true;
    }

    /**
     * Remove value from cache
     */
    public function forget(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        $files = glob($this->path . '/*');

        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Get file path for key
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        return $this->path . '/' . $hash . '.cache';
    }
}
