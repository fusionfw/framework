<?php

namespace Fusion\Core\Plugin;

use Fusion\Core\Container;
use Fusion\Core\Logger;
use Fusion\Core\Config;

/**
 * Plugin Manager
 */
class PluginManager
{
    private $container;
    private $logger;
    private $config;
    private $plugins = [];
    private $pluginsPath;

    public function __construct(Container $container, Logger $logger, Config $config)
    {
        $this->container = $container;
        $this->logger = $logger;
        $this->config = $config;
        $this->pluginsPath = dirname(__DIR__, 2) . '/plugins';

        $this->loadPlugins();
    }

    /**
     * Load all plugins
     */
    private function loadPlugins(): void
    {
        if (!is_dir($this->pluginsPath)) {
            mkdir($this->pluginsPath, 0755, true);
            return;
        }

        $directories = glob($this->pluginsPath . '/*', GLOB_ONLYDIR);

        foreach ($directories as $directory) {
            $pluginName = basename($directory);
            $pluginFile = $directory . '/' . $pluginName . '.php';

            if (file_exists($pluginFile)) {
                $this->loadPlugin($pluginName, $pluginFile);
            }
        }
    }

    /**
     * Load single plugin
     */
    private function loadPlugin(string $name, string $file): void
    {
        try {
            require_once $file;

            $className = "\\Flexify\\Plugins\\{$name}\\{$name}";

            if (class_exists($className)) {
                $plugin = new $className();

                if ($plugin instanceof PluginInterface) {
                    $this->plugins[$name] = $plugin;
                    $this->logger->info("Plugin loaded: {$name}");
                } else {
                    $this->logger->error("Plugin {$name} does not implement PluginInterface");
                }
            } else {
                $this->logger->error("Plugin class not found: {$className}");
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to load plugin {$name}: " . $e->getMessage());
        }
    }

    /**
     * Get all plugins
     */
    public function getPlugins(): array
    {
        return $this->plugins;
    }

    /**
     * Get plugin by name
     */
    public function getPlugin(string $name): ?PluginInterface
    {
        return $this->plugins[$name] ?? null;
    }

    /**
     * Install plugin
     */
    public function install(string $name): bool
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            $this->logger->error("Plugin not found: {$name}");
            return false;
        }

        try {
            $result = $plugin->install();

            if ($result) {
                $this->logger->info("Plugin installed: {$name}");
            } else {
                $this->logger->error("Plugin installation failed: {$name}");
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Plugin installation error: {$name} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Uninstall plugin
     */
    public function uninstall(string $name): bool
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            $this->logger->error("Plugin not found: {$name}");
            return false;
        }

        try {
            $result = $plugin->uninstall();

            if ($result) {
                $this->logger->info("Plugin uninstalled: {$name}");
            } else {
                $this->logger->error("Plugin uninstallation failed: {$name}");
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Plugin uninstallation error: {$name} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Activate plugin
     */
    public function activate(string $name): bool
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            $this->logger->error("Plugin not found: {$name}");
            return false;
        }

        try {
            $result = $plugin->activate();

            if ($result) {
                $this->logger->info("Plugin activated: {$name}");
            } else {
                $this->logger->error("Plugin activation failed: {$name}");
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Plugin activation error: {$name} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Deactivate plugin
     */
    public function deactivate(string $name): bool
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            $this->logger->error("Plugin not found: {$name}");
            return false;
        }

        try {
            $result = $plugin->deactivate();

            if ($result) {
                $this->logger->info("Plugin deactivated: {$name}");
            } else {
                $this->logger->error("Plugin deactivation failed: {$name}");
            }

            return $result;
        } catch (\Exception $e) {
            $this->logger->error("Plugin deactivation error: {$name} - " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check plugin dependencies
     */
    public function checkDependencies(string $name): array
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            return ['error' => 'Plugin not found'];
        }

        $dependencies = $plugin->getDependencies();
        $missing = [];

        foreach ($dependencies as $dependency) {
            if (!$this->getPlugin($dependency)) {
                $missing[] = $dependency;
            }
        }

        return $missing;
    }

    /**
     * Get plugin status
     */
    public function getStatus(string $name): array
    {
        $plugin = $this->getPlugin($name);

        if (!$plugin) {
            return ['status' => 'not_found'];
        }

        return [
            'name' => $plugin->getName(),
            'version' => $plugin->getVersion(),
            'description' => $plugin->getDescription(),
            'dependencies' => $plugin->getDependencies(),
            'compatible' => $plugin->isCompatible('1.0.0')
        ];
    }
}
