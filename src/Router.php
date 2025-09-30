<?php

namespace Fusion;

use Fusion\Request;
use Fusion\Response;

/**
 * Simple Router Class
 */
class Router
{
    private $routes = [];
    private $middlewares = [];
    private $groupPrefix = '';
    private $groupMiddleware = [];

    /**
     * Add GET route
     */
    public function get(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add POST route
     */
    public function post(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Add PATCH route
     */
    public function patch(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    /**
     * Add any method route
     */
    public function any(string $path, $handler, array $middleware = []): self
    {
        return $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, $handler, $middleware);
    }

    /**
     * Add route group
     */
    public function group(array $attributes, callable $callback): self
    {
        $oldPrefix = $this->groupPrefix;
        $oldMiddleware = $this->groupMiddleware;

        if (isset($attributes['prefix'])) {
            $this->groupPrefix = $oldPrefix . $attributes['prefix'];
        }

        if (isset($attributes['middleware'])) {
            $this->groupMiddleware = array_merge($oldMiddleware, $attributes['middleware']);
        }

        $callback($this);

        $this->groupPrefix = $oldPrefix;
        $this->groupMiddleware = $oldMiddleware;

        return $this;
    }

    /**
     * Add route
     */
    private function addRoute($methods, string $path, $handler, array $middleware = []): self
    {
        $methods = is_array($methods) ? $methods : [$methods];
        $path = $this->groupPrefix . $path;
        $middleware = array_merge($this->groupMiddleware, $middleware);

        foreach ($methods as $method) {
            $this->routes[] = [
                'method' => $method,
                'path' => $path,
                'handler' => $handler,
                'middleware' => $middleware
            ];
        }

        return $this;
    }

    /**
     * Dispatch request
     */
    public function dispatch(Request $request): Response
    {
        $path = $request->getPath();
        $method = $request->getMethod();

        foreach ($this->routes as $route) {
            if ($this->matchRoute($route, $method, $path)) {
                return $this->executeRoute($route, $request);
            }
        }

        return new Response('Not Found', 404);
    }

    /**
     * Match route
     */
    private function matchRoute(array $route, string $method, string $path): bool
    {
        if ($route['method'] !== $method) {
            return false;
        }

        $pattern = $this->convertToRegex($route['path']);
        return preg_match($pattern, $path);
    }

    /**
     * Convert route path to regex
     */
    private function convertToRegex(string $path): string
    {
        $path = preg_replace('/\{([^}]+)\}/', '([^/]+)', $path);
        return '#^' . $path . '$#';
    }

    /**
     * Execute route
     */
    private function executeRoute(array $route, Request $request): Response
    {
        // Execute middleware
        foreach ($route['middleware'] as $middleware) {
            $response = $this->executeMiddleware($middleware, $request);
            if ($response instanceof Response) {
                return $response;
            }
        }

        // Execute handler
        $handler = $route['handler'];

        if (is_string($handler)) {
            // Controller@method format
            if (strpos($handler, '@') !== false) {
                [$controller, $method] = explode('@', $handler);
                $controller = "\\App\\Modules\\{$controller}";

                if (class_exists($controller)) {
                    $instance = new $controller();
                    if (method_exists($instance, $method)) {
                        return $instance->$method($request);
                    }
                }
            }
        } elseif (is_callable($handler)) {
            return $handler($request);
        }

        return new Response('Handler not found', 500);
    }

    /**
     * Execute middleware
     */
    private function executeMiddleware($middleware, Request $request): ?Response
    {
        if (is_string($middleware)) {
            $middlewareClass = "\\App\\Middleware\\{$middleware}";
            if (class_exists($middlewareClass)) {
                $instance = new $middlewareClass();
                return $instance->handle($request);
            }
        } elseif (is_callable($middleware)) {
            return $middleware($request);
        }

        return null;
    }

    /**
     * Get all routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
