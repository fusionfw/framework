<?php

namespace Fusion\Core;

use Fusion\Core\Request;
use Fusion\Core\Response;
use Fusion\Core\Container;

/**
 * Base Controller Class - Fusion Framework
 * Combines the best features from both frameworks
 */
abstract class Controller
{
    protected $container;
    protected $request;
    protected array $data = [];

    public function __construct()
    {
        $this->container = Container::getInstance();
        $this->request = $this->container->make(Request::class);
    }

    /**
     * Return JSON response
     */
    protected function json($data, int $statusCode = 200): Response
    {
        return (new Response())->json($data, $statusCode);
    }

    /**
     * Return view response
     */
    protected function view(string $view, array $data = [], int $statusCode = 200): Response
    {
        return (new Response())->view($view, $data, $statusCode);
    }

    /**
     * Return view response (alternative method)
     */
    protected function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);

        // Extract data to variables
        extract($this->data);

        // Include view file
        $viewPath = $this->getViewPath($view);

        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }
    }

    /**
     * Return redirect response
     */
    protected function redirect(string $url, int $statusCode = 302): Response
    {
        return (new Response())->redirect($url, $statusCode);
    }

    /**
     * Return success response
     */
    protected function success($data = null, string $message = 'Success', int $statusCode = 200): Response
    {
        return $this->json([
            'success' => true,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Return error response
     */
    protected function error(string $message = 'Error', int $statusCode = 400, $data = null): Response
    {
        return $this->json([
            'success' => false,
            'message' => $message,
            'data' => $data
        ], $statusCode);
    }

    /**
     * Get input data
     */
    protected function input(string $key, $default = null)
    {
        return $this->request->input($key, $default);
    }

    /**
     * Get all input data
     */
    protected function getInput(): array
    {
        return $this->request->input();
    }

    /**
     * Validate request data
     */
    protected function validate(array $rules, array $data = null): array
    {
        $data = $data ?: $this->request->input();
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $fieldRules = is_array($rule) ? $rule : explode('|', $rule);

            foreach ($fieldRules as $fieldRule) {
                $error = $this->validateField($field, $value, $fieldRule);
                if ($error) {
                    $errors[$field][] = $error;
                }
            }
        }

        return $errors;
    }

    /**
     * Validate single field (Enhanced)
     */
    private function validateField(string $field, $value, string $rule): ?string
    {
        $params = explode(':', $rule);
        $ruleName = $params[0];
        $ruleValue = $params[1] ?? null;

        switch ($ruleName) {
            case 'required':
                if (empty($value)) {
                    return "The {$field} field is required.";
                }
                break;

            case 'email':
                if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "The {$field} field must be a valid email address.";
                }
                break;

            case 'min':
                if (!empty($value) && strlen($value) < $ruleValue) {
                    return "The {$field} field must be at least {$ruleValue} characters.";
                }
                break;

            case 'max':
                if (!empty($value) && strlen($value) > $ruleValue) {
                    return "The {$field} field must not exceed {$ruleValue} characters.";
                }
                break;

            case 'numeric':
                if (!empty($value) && !is_numeric($value)) {
                    return "The {$field} field must be numeric.";
                }
                break;

            case 'unique':
                // This would need to be implemented with database checking
                break;
        }

        return null;
    }

    /**
     * CSRF token generation
     */
    protected function csrf(): string
    {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    /**
     * CSRF token verification
     */
    protected function verifyCsrf(): bool
    {
        $token = $this->input('csrf_token');
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }

    /**
     * Get view path
     */
    private function getViewPath(string $view): string
    {
        // Get calling class to determine module
        $callingClass = get_called_class();
        $parts = explode('\\', $callingClass);

        if (count($parts) >= 3 && $parts[0] === 'App' && $parts[1] === 'Modules') {
            $module = $parts[2];
            return __DIR__ . "/../app/modules/{$module}/Views/{$view}.php";
        }

        return __DIR__ . "/../app/Views/{$view}.php";
    }

    /**
     * Get service from container
     */
    protected function service(string $service)
    {
        return $this->container->make($service);
    }

    /**
     * Get repository from container
     */
    protected function repository(string $repository)
    {
        return $this->container->make($repository);
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
