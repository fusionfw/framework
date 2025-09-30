<?php

namespace Fusion\Session;

use Fusion\\Config;

/**
 * Session Manager
 */
class SessionManager
{
    private $config;
    private $started = false;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Start session
     */
    public function start(): void
    {
        if ($this->started) {
            return;
        }

        $config = $this->config->get('app.session', []);

        // Set session configuration
        if (isset($config['lifetime'])) {
            ini_set('session.gc_maxlifetime', $config['lifetime'] * 60);
        }

        if (isset($config['path'])) {
            ini_set('session.cookie_path', $config['path']);
        }

        if (isset($config['domain'])) {
            ini_set('session.cookie_domain', $config['domain']);
        }

        if (isset($config['secure'])) {
            ini_set('session.cookie_secure', $config['secure'] ? 1 : 0);
        }

        if (isset($config['httponly'])) {
            ini_set('session.cookie_httponly', $config['httponly'] ? 1 : 0);
        }

        // Start session
        session_start();
        $this->started = true;
    }

    /**
     * Get session value
     */
    public function get(string $key, $default = null)
    {
        $this->start();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, $value): void
    {
        $this->start();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        $this->start();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session value
     */
    public function remove(string $key): void
    {
        $this->start();
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        $this->start();
        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->start();
        $_SESSION = [];
    }

    /**
     * Regenerate session ID
     */
    public function regenerate(): void
    {
        $this->start();
        session_regenerate_id(true);
    }

    /**
     * Destroy session
     */
    public function destroy(): void
    {
        $this->start();
        session_destroy();
        $this->started = false;
    }

    /**
     * Flash data (one-time use)
     */
    public function flash(string $key, $value = null)
    {
        if ($value !== null) {
            $this->set("_flash.{$key}", $value);
            return;
        }

        $value = $this->get("_flash.{$key}");
        $this->remove("_flash.{$key}");
        return $value;
    }

    /**
     * Keep flash data for next request
     */
    public function keep(array $keys): void
    {
        foreach ($keys as $key) {
            $this->set("_flash.keep.{$key}", true);
        }
    }

    /**
     * Get session ID
     */
    public function getId(): string
    {
        $this->start();
        return session_id();
    }

    /**
     * Set session ID
     */
    public function setId(string $id): void
    {
        if ($this->started) {
            throw new \Exception('Cannot set session ID after session has started');
        }

        session_id($id);
    }
}
