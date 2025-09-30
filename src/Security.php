<?php

namespace Fusion;

/**
 * Security Helper Class
 */
class Security
{
    /**
     * Generate CSRF token
     */
    public static function generateCsrfToken(): string
    {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    /**
     * Verify CSRF token
     */
    public static function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }

    /**
     * Hash password
     */
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verify password
     */
    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /**
     * Sanitize input (XSS protection)
     */
    public static function sanitizeInput($input)
    {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }

        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Escape output
     */
    public static function escapeOutput(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generate random string
     */
    public static function randomString(int $length = 32): string
    {
        return bin2hex(random_bytes($length / 2));
    }

    /**
     * Validate email
     */
    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Validate URL
     */
    public static function validateUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check if string is safe
     */
    public static function isSafeString(string $string): bool
    {
        // Check for common XSS patterns
        $patterns = [
            '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi',
            '/<iframe\b[^<]*(?:(?!<\/iframe>)<[^<]*)*<\/iframe>/mi',
            '/javascript:/i',
            '/on\w+\s*=/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $string)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Generate secure filename
     */
    public static function secureFilename(string $filename): string
    {
        // Remove dangerous characters
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);

        // Remove multiple dots
        $filename = preg_replace('/\.{2,}/', '.', $filename);

        // Ensure it doesn't start with dot
        $filename = ltrim($filename, '.');

        return $filename;
    }

    /**
     * Rate limiting
     */
    public static function rateLimit(string $key, int $maxAttempts = 60, int $timeWindow = 3600): bool
    {
        $cacheFile = sys_get_temp_dir() . '/rate_limit_' . md5($key) . '.json';

        $data = [];
        if (file_exists($cacheFile)) {
            $data = json_decode(file_get_contents($cacheFile), true) ?: [];
        }

        $now = time();
        $windowStart = $now - $timeWindow;

        // Remove old attempts
        $data = array_filter($data, function ($timestamp) use ($windowStart) {
            return $timestamp > $windowStart;
        });

        // Check if limit exceeded
        if (count($data) >= $maxAttempts) {
            return false;
        }

        // Add current attempt
        $data[] = $now;
        file_put_contents($cacheFile, json_encode($data));

        return true;
    }
}
