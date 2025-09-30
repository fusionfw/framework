<?php

namespace Flexify\Plugins\Payment;

use Fusion\Core\Plugin\PluginInterface;

/**
 * Payment Plugin
 */
class Payment implements PluginInterface
{
    public function getName(): string
    {
        return 'Payment';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Payment processing plugin with multiple gateway support';
    }

    public function install(): bool
    {
        // Create payment tables
        $this->createPaymentTables();

        // Register payment routes
        $this->registerRoutes();

        // Register payment services
        $this->registerServices();

        return true;
    }

    public function uninstall(): bool
    {
        // Drop payment tables
        $this->dropPaymentTables();

        // Remove payment routes
        $this->removeRoutes();

        return true;
    }

    public function activate(): bool
    {
        // Enable payment functionality
        return true;
    }

    public function deactivate(): bool
    {
        // Disable payment functionality
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
     * Create payment tables
     */
    private function createPaymentTables(): void
    {
        $connection = \Fusion\Core\Database\Connection::getInstance();

        $sql = "CREATE TABLE IF NOT EXISTS payments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            amount DECIMAL(10,2) NOT NULL,
            currency VARCHAR(3) DEFAULT 'USD',
            gateway VARCHAR(50) NOT NULL,
            transaction_id VARCHAR(255),
            status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        $connection->query($sql);
    }

    /**
     * Drop payment tables
     */
    private function dropPaymentTables(): void
    {
        $connection = \Fusion\Core\Database\Connection::getInstance();
        $connection->query("DROP TABLE IF EXISTS payments");
    }

    /**
     * Register payment routes
     */
    private function registerRoutes(): void
    {
        // Routes will be registered in the main application
    }

    /**
     * Register payment services
     */
    private function registerServices(): void
    {
        // Services will be registered in the container
    }
}
