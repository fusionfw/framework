<?php

namespace Fusion;

/**
 * Configuration Manager
 */
class Config
{
    private $config = [];
    private $configPath;

    public function __construct(string $configPath)
    {
        $this->configPath = $configPath;
        $this->loadEnv();
        $this->loadConfig();
    }

    /**
     * Load all configuration files
     */
    private function loadConfig()
    {
        if (!is_dir($this->configPath)) {
            return;
        }

        $files = glob($this->configPath . '/*.php');

        foreach ($files as $file) {
            $key = basename($file, '.php');
            $this->config[$key] = require $file;
        }
    }

    /**
     * Get configuration value
     */
    public function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (is_array($value) && array_key_exists($k, $value)) {
                $value = $value[$k];
            } else {
                return $default;
            }
        }

        return $value;
    }

    /**
     * Set configuration value
     */
    public function set(string $key, $value): self
    {
        $keys = explode('.', $key);
        $config = &$this->config;

        foreach ($keys as $k) {
            if (!isset($config[$k]) || !is_array($config[$k])) {
                $config[$k] = [];
            }
            $config = &$config[$k];
        }

        $config = $value;
        return $this;
    }

    /**
     * Check if configuration exists
     */
    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    /**
     * Get all configuration
     */
    public function all(): array
    {
        return $this->config;
    }

    /**
     * Load environment variables with multiple sources support
     */
    public function loadEnv(string $envFile = '.env'): self
    {
        // 1. Try .env file first (development)
        $this->loadEnvFile($envFile);

        // 2. Try Docker secrets (production)
        $this->loadDockerSecrets();

        // 3. Try cloud secrets (if configured)
        if ($this->isCloudEnvironment()) {
            $this->loadCloudSecrets();
        }

        // 4. Load system environment variables
        $this->loadSystemEnvironment();

        return $this;
    }

    /**
     * Load .env file
     */
    private function loadEnvFile(string $envFile): void
    {
        $envPath = dirname($this->configPath, 1) . '/' . $envFile;

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') === false) {
                    continue;
                }

                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);

                // Remove BOM if present
                $name = preg_replace('/^\xEF\xBB\xBF/', '', $name);

                // Remove quotes if present
                $value = trim($value, '"\'');

                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                    putenv("$name=$value");
                }
            }
        }
    }

    /**
     * Load Docker secrets
     */
    private function loadDockerSecrets(): void
    {
        $secretsPath = '/run/secrets/';

        if (is_dir($secretsPath)) {
            $files = glob($secretsPath . '*');

            foreach ($files as $file) {
                $key = strtoupper(basename($file));
                $value = trim(file_get_contents($file));

                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Load cloud secrets (Azure Key Vault, AWS Secrets Manager)
     */
    private function loadCloudSecrets(): void
    {
        // Azure Key Vault
        if (isset($_ENV['AZURE_KEY_VAULT_URL'])) {
            $this->loadAzureKeyVault();
        }

        // AWS Secrets Manager
        if (isset($_ENV['AWS_SECRETS_MANAGER_REGION'])) {
            $this->loadAwsSecrets();
        }
    }

    /**
     * Load Azure Key Vault secrets
     */
    private function loadAzureKeyVault(): void
    {
        // Implementation for Azure Key Vault
        // This would require Azure SDK
    }

    /**
     * Load AWS Secrets Manager secrets
     */
    private function loadAwsSecrets(): void
    {
        // Implementation for AWS Secrets Manager
        // This would require AWS SDK
    }

    /**
     * Check if running in cloud environment
     */
    private function isCloudEnvironment(): bool
    {
        return isset($_ENV['AZURE_KEY_VAULT_URL']) ||
            isset($_ENV['AWS_SECRETS_MANAGER_REGION']) ||
            isset($_ENV['GOOGLE_CLOUD_PROJECT']);
    }

    /**
     * Load system environment variables
     */
    private function loadSystemEnvironment(): void
    {
        // System environment variables are already available in $_ENV
        // This method can be used for additional processing if needed
    }
}
