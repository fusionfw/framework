<?php

namespace App\Middleware;

use Fusion\Core\Middleware;
use Fusion\Core\Request;
use Fusion\Core\Response;
use Fusion\Core\Security;

/**
 * CSRF Protection Middleware
 */
class CsrfMiddleware extends Middleware
{
    public function handle(Request $request): ?Response
    {
        // Skip CSRF for GET requests
        if ($request->isGet()) {
            return null;
        }

        // Skip CSRF for AJAX requests with proper header
        if ($request->isAjax() && $request->header('X-CSRF-Token')) {
            $token = $request->header('X-CSRF-Token');
        } else {
            $token = $request->input('_token');
        }

        if (!$token || !Security::verifyCsrfToken($token)) {
            return new Response('CSRF token mismatch', 403);
        }

        return null;
    }
}
