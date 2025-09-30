<?php

namespace Fusion;

use Fusion\\Container;

/**
 * Base Service Class - Fusion of Flexify + Flight
 * Combines the best features from both frameworks
 */
abstract class Service
{
    protected $container;
    protected $repository;

    public function __construct($repository = null)
    {
        $this->container = Container::getInstance();
        $this->repository = $repository;
    }

    /**
     * Get repository from container
     */
    protected function repository(string $repository)
    {
        return $this->container->make($repository);
    }

    /**
     * Validate data (Flight style)
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $ruleArray = explode('|', $rule);

            foreach ($ruleArray as $singleRule) {
                if ($singleRule === 'required' && empty($value)) {
                    $errors[$field] = "Field {$field} is required";
                } elseif (strpos($singleRule, 'min:') === 0) {
                    $min = (int) substr($singleRule, 4);
                    if (strlen($value) < $min) {
                        $errors[$field] = "Field {$field} must be at least {$min} characters";
                    }
                } elseif (strpos($singleRule, 'max:') === 0) {
                    $max = (int) substr($singleRule, 4);
                    if (strlen($value) > $max) {
                        $errors[$field] = "Field {$field} must not exceed {$max} characters";
                    }
                } elseif ($singleRule === 'email' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = "Field {$field} must be a valid email";
                } elseif ($singleRule === 'unique') {
                    if ($this->repository && method_exists($this->repository, 'findBy')) {
                        $existing = $this->repository->findBy($field, $value);
                        if ($existing) {
                            $errors[$field] = "Field {$field} must be unique";
                        }
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * Sanitize data (Flight style)
     */
    protected function sanitize(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    /**
     * Hash password (Flight style)
     */
    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    /**
     * Verify password (Flight style)
     */
    protected function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Generate token (Flight style)
     */
    protected function generateToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Log message (Flight style)
     */
    protected function log(string $message, string $level = 'info'): void
    {
        $this->logger()->log($level, $message);
    }

    /**
     * Get logger
     */
    protected function logger(): Logger
    {
        return $this->container->make(Logger::class);
    }

    /**
     * Get config
     */
    protected function config(): Config
    {
        return $this->container->make(Config::class);
    }

    /**
     * Get auth manager
     */
    protected function auth(): Auth\AuthManager
    {
        return $this->container->make(Auth\AuthManager::class);
    }

    /**
     * Get session manager
     */
    protected function session(): Session\SessionManager
    {
        return $this->container->make(Session\SessionManager::class);
    }

    /**
     * Get cache manager
     */
    protected function cache(): Cache\CacheManager
    {
        return $this->container->make(Cache\CacheManager::class);
    }
}
