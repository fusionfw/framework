<?php

namespace App\Middleware;

use Fusion\Core\Middleware;
use Fusion\Core\Request;
use Fusion\Core\Response;

/**
 * Authentication Middleware
 */
class AuthMiddleware extends Middleware
{
    public function handle(Request $request): ?Response
    {
        // Check if user is authenticated
        if (!$this->isAuthenticated($request)) {
            return new Response('Unauthorized', 401);
        }

        return null;
    }

    /**
     * Check if user is authenticated
     */
    private function isAuthenticated(Request $request): bool
    {
        // Check session
        if (isset($_SESSION['user_id'])) {
            return true;
        }

        // Check API token
        $token = $request->header('Authorization');
        if ($token && $this->validateApiToken($token)) {
            return true;
        }

        return false;
    }

    /**
     * Validate API token
     */
    private function validateApiToken(string $token): bool
    {
        // Remove "Bearer " prefix
        $token = str_replace('Bearer ', '', $token);

        // Simple token validation (implement your own logic)
        return !empty($token) && strlen($token) > 10;
    }
}
