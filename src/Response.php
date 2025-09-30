<?php

namespace Fusion;

/**
 * HTTP Response Class
 */
class Response
{
    private $content;
    private $statusCode;
    private $headers;

    public function __construct($content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * Set content
     */
    public function setContent($content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * Set status code
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Set header
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    /**
     * Set multiple headers
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * Set JSON response
     */
    public function json($data, int $statusCode = 200): self
    {
        $this->setHeader('Content-Type', 'application/json');
        $this->setContent(json_encode($data));
        $this->setStatusCode($statusCode);
        return $this;
    }

    /**
     * Set redirect response
     */
    public function redirect(string $url, int $statusCode = 302): self
    {
        $this->setHeader('Location', $url);
        $this->setStatusCode($statusCode);
        $this->setContent('');
        return $this;
    }

    /**
     * Set view response
     */
    public function view(string $view, array $data = [], int $statusCode = 200): self
    {
        $content = $this->renderView($view, $data);
        $this->setContent($content);
        $this->setStatusCode($statusCode);
        return $this;
    }

    /**
     * Render view file
     */
    private function renderView(string $view, array $data = []): string
    {
        // Extract data to variables
        extract($data);

        // Start output buffering
        ob_start();

        // Include view file
        $viewPath = $this->getViewPath($view);
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            throw new \Exception("View not found: {$view}");
        }

        return ob_get_clean();
    }

    /**
     * Get view file path
     */
    private function getViewPath(string $view): string
    {
        // Parse module.view format
        $parts = explode('.', $view);
        if (count($parts) === 2) {
            $module = $parts[0];
            $file = $parts[1];
            return dirname(__DIR__, 2) . "/app/modules/{$module}/Views/{$file}.php";
        }

        // Default to app/views
        return dirname(__DIR__, 2) . "/app/views/{$view}.php";
    }

    /**
     * Send response
     */
    public function send()
    {
        // Set status code
        http_response_code($this->statusCode);

        // Set headers
        foreach ($this->headers as $name => $value) {
            header("{$name}: {$value}");
        }

        // Output content
        echo $this->content;
    }

    /**
     * Get content
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }
}
