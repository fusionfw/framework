<?php

namespace Fusion;

use Fusion\\Request;
use Fusion\\Response;

/**
 * Base Middleware Class
 */
abstract class Middleware
{
    /**
     * Handle the request
     */
    abstract public function handle(Request $request): ?Response;

    /**
     * Call next middleware
     */
    protected function next(Request $request): ?Response
    {
        return null;
    }
}
