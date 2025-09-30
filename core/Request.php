<?php

namespace Fusion\Core;

/**
 * HTTP Request Class
 */
class Request
{
    private $get;
    private $post;
    private $server;
    private $files;
    private $cookies;
    private $headers;
    private $method;
    private $uri;
    private $path;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->files = $_FILES;
        $this->cookies = $_COOKIE;
        $this->headers = $this->getAllHeaders();
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path = parse_url($this->uri, PHP_URL_PATH);
    }

    /**
     * Create request from globals
     */
    public static function createFromGlobals(): self
    {
        return new self();
    }

    /**
     * Get all headers
     */
    private function getAllHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            foreach ($_SERVER as $name => $value) {
                if (strpos($name, 'HTTP_') === 0) {
                    $name = str_replace('_', '-', substr($name, 5));
                    $headers[$name] = $value;
                }
            }
        }

        return $headers;
    }

    /**
     * Get request method
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Get request URI
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Get request path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Get GET parameter
     */
    public function get(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->get;
        }
        return $this->get[$key] ?? $default;
    }

    /**
     * Get POST parameter
     */
    public function post(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->post;
        }
        return $this->post[$key] ?? $default;
    }

    /**
     * Get input (GET or POST)
     */
    public function input(string $key = null, $default = null)
    {
        $input = array_merge($this->get, $this->post);

        if ($key === null) {
            return $input;
        }
        return $input[$key] ?? $default;
    }

    /**
     * Get file upload
     */
    public function file(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * Get cookie
     */
    public function cookie(string $key, $default = null)
    {
        return $this->cookies[$key] ?? $default;
    }

    /**
     * Get header
     */
    public function header(string $key, $default = null)
    {
        $key = strtolower($key);
        return $this->headers[$key] ?? $default;
    }

    /**
     * Check if request is AJAX
     */
    public function isAjax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * Check if request is POST
     */
    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    /**
     * Check if request is GET
     */
    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    /**
     * Get JSON input
     */
    public function json(string $key = null, $default = null)
    {
        $json = json_decode(file_get_contents('php://input'), true);

        if ($key === null) {
            return $json;
        }
        return $json[$key] ?? $default;
    }

    /**
     * Get CSRF token
     */
    public function csrfToken(): string
    {
        if (!isset($_SESSION['_token'])) {
            $_SESSION['_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_token'];
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken(string $token): bool
    {
        return isset($_SESSION['_token']) && hash_equals($_SESSION['_token'], $token);
    }
}
