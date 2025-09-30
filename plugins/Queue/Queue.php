<?php

namespace Flexify\Plugins\Queue;

use Fusion\Core\Plugin\PluginInterface;

/**
 * Queue Plugin
 */
class Queue implements PluginInterface
{
    public function getName(): string
    {
        return 'Queue';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Job queue system with multiple drivers support';
    }

    public function install(): bool
    {
        // Create queue tables
        $this->createQueueTables();

        // Register queue services
        $this->registerServices();

        return true;
    }

    public function uninstall(): bool
    {
        // Drop queue tables
        $this->dropQueueTables();

        return true;
    }

    public function activate(): bool
    {
        // Enable queue functionality
        return true;
    }

    public function deactivate(): bool
    {
        // Disable queue functionality
        return true;
    }

    public function getDependencies(): array
    {
        return [];
    }

    public function isCompatible(string $frameworkVersion): bool
    {
        return version_compare($frameworkVersion, '1.0.0', '>=');
    }

    /**
     * Create queue tables
     */
    private function createQueueTables(): void
    {
        $connection = \Fusion\Core\Database\Connection::getInstance();

        $sql = "CREATE TABLE IF NOT EXISTS jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            queue VARCHAR(255) NOT NULL,
            payload LONGTEXT NOT NULL,
            attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            reserved_at INT UNSIGNED NULL,
            available_at INT UNSIGNED NOT NULL,
            created_at INT UNSIGNED NOT NULL
        )";

        $connection->query($sql);
    }

    /**
     * Drop queue tables
     */
    private function dropQueueTables(): void
    {
        $connection = \Fusion\Core\Database\Connection::getInstance();
        $connection->query("DROP TABLE IF EXISTS jobs");
    }

    /**
     * Register queue services
     */
    private function registerServices(): void
    {
        // Services will be registered in the container
    }
}
