<?php

namespace Fusion\Auth;

use Fusion\\Session\SessionManager;
use Fusion\\Cache\CacheManager;
use Fusion\\Security;
use Fusion\\Logger;

/**
 * Authentication Manager
 */
class AuthManager
{
    private $session;
    private $cache;
    private $logger;
    private $userModel;
    private $guard = 'web';

    public function __construct(SessionManager $session, CacheManager $cache, Logger $logger)
    {
        $this->session = $session;
        $this->cache = $cache;
        $this->logger = $logger;
    }

    /**
     * Set user model
     */
    public function setUserModel(string $model): self
    {
        $this->userModel = $model;
        return $this;
    }

    /**
     * Set guard
     */
    public function guard(string $guard): self
    {
        $this->guard = $guard;
        return $this;
    }

    /**
     * Check if user is authenticated
     */
    public function check(): bool
    {
        return $this->session->has("auth.{$this->guard}.user_id");
    }

    /**
     * Get authenticated user
     */
    public function user()
    {
        if (!$this->check()) {
            return null;
        }

        $userId = $this->session->get("auth.{$this->guard}.user_id");

        // Try cache first
        $cacheKey = "auth.user.{$userId}";
        $user = $this->cache->get($cacheKey);

        if ($user) {
            return $user;
        }

        // Load from database
        $user = $this->loadUser($userId);

        if ($user) {
            $this->cache->set($cacheKey, $user, 3600); // Cache for 1 hour
        }

        return $user;
    }

    /**
     * Get user ID
     */
    public function id(): ?int
    {
        if (!$this->check()) {
            return null;
        }

        return $this->session->get("auth.{$this->guard}.user_id");
    }

    /**
     * Login user
     */
    public function login($user, bool $remember = false): bool
    {
        $userId = is_object($user) ? $user->getKey() : $user;

        $this->session->set("auth.{$this->guard}.user_id", $userId);

        if ($remember) {
            $this->createRememberToken($userId);
        }

        $this->logger->info('User logged in', ['user_id' => $userId, 'guard' => $this->guard]);

        return true;
    }

    /**
     * Logout user
     */
    public function logout(): bool
    {
        if (!$this->check()) {
            return false;
        }

        $userId = $this->id();

        // Clear remember token
        $this->clearRememberToken($userId);

        // Clear session
        $this->session->remove("auth.{$this->guard}.user_id");

        // Clear cache
        $this->cache->forget("auth.user.{$userId}");

        $this->logger->info('User logged out', ['user_id' => $userId, 'guard' => $this->guard]);

        return true;
    }

    /**
     * Attempt login with credentials
     */
    public function attempt(array $credentials, bool $remember = false): bool
    {
        $user = $this->getUserByCredentials($credentials);

        if (!$user) {
            $this->logger->warning('Login attempt failed - user not found', ['credentials' => array_keys($credentials)]);
            return false;
        }

        if (!$this->validateCredentials($user, $credentials)) {
            $this->logger->warning('Login attempt failed - invalid credentials', ['user_id' => $user->getKey()]);
            return false;
        }

        return $this->login($user, $remember);
    }

    /**
     * Register new user
     */
    public function register(array $data): ?object
    {
        // Hash password
        if (isset($data['password'])) {
            $data['password'] = Security::hashPassword($data['password']);
        }

        // Create user
        $user = $this->userModel::create($data);

        if ($user) {
            $this->logger->info('User registered', ['user_id' => $user->getKey()]);
        }

        return $user;
    }

    /**
     * Load user by ID
     */
    private function loadUser(int $userId)
    {
        if (!$this->userModel) {
            return null;
        }

        return $this->userModel::find($userId);
    }

    /**
     * Get user by credentials
     */
    private function getUserByCredentials(array $credentials)
    {
        if (!$this->userModel) {
            return null;
        }

        $query = $this->userModel::query();

        foreach ($credentials as $key => $value) {
            if ($key === 'password') {
                continue;
            }

            $query->where($key, $value);
        }

        return $query->first();
    }

    /**
     * Validate user credentials
     */
    private function validateCredentials($user, array $credentials): bool
    {
        if (!isset($credentials['password'])) {
            return false;
        }

        return Security::verifyPassword($credentials['password'], $user->password);
    }

    /**
     * Create remember token
     */
    private function createRememberToken(int $userId): void
    {
        $token = Security::randomString(60);

        // Store in database
        $this->userModel::query()
            ->where('id', $userId)
            ->update(['remember_token' => $token]);

        // Set cookie
        setcookie("remember_{$this->guard}", $token, time() + (30 * 24 * 60 * 60), '/'); // 30 days
    }

    /**
     * Clear remember token
     */
    private function clearRememberToken(int $userId): void
    {
        // Clear from database
        $this->userModel::query()
            ->where('id', $userId)
            ->update(['remember_token' => null]);

        // Clear cookie
        setcookie("remember_{$this->guard}", '', time() - 3600, '/');
    }

    /**
     * Check remember token
     */
    public function checkRememberToken(): bool
    {
        $token = $_COOKIE["remember_{$this->guard}"] ?? null;

        if (!$token) {
            return false;
        }

        $user = $this->userModel::query()
            ->where('remember_token', $token)
            ->first();

        if (!$user) {
            return false;
        }

        return $this->login($user);
    }
}
