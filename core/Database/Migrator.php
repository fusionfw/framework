<?php

namespace Fusion\Core\Database;

use Fusion\Core\Database\Connection;
use Fusion\Core\Logger;
use PDO;

/**
 * Database Migrator
 */
class Migrator
{
    private $connection;
    private $logger;
    private $migrationsPath;

    public function __construct(Connection $connection = null, Logger $logger = null)
    {
        $this->connection = $connection ?: Connection::getInstance();
        $this->logger = $logger ?: new \Fusion\Core\Logger(dirname(__DIR__, 2) . '/storage/logs', Logger::INFO);
        $this->migrationsPath = dirname(__DIR__, 2) . '/database/migrations';

        $this->ensureMigrationsTable();
    }

    /**
     * Run all pending migrations
     */
    public function run(): void
    {
        $migrations = $this->getPendingMigrations();

        if (empty($migrations)) {
            $this->logger->info('No pending migrations');
            return;
        }

        $this->logger->info('Running ' . count($migrations) . ' migrations');

        foreach ($migrations as $migration) {
            $this->runMigration($migration);
        }

        $this->logger->info('All migrations completed');
    }

    /**
     * Rollback last batch of migrations
     */
    public function rollback(): void
    {
        $migrations = $this->getLastBatchMigrations();

        if (empty($migrations)) {
            $this->logger->info('No migrations to rollback');
            return;
        }

        $this->logger->info('Rolling back ' . count($migrations) . ' migrations');

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
        }

        $this->logger->info('Rollback completed');
    }

    /**
     * Reset all migrations
     */
    public function reset(): void
    {
        $migrations = $this->getAllMigrations();

        if (empty($migrations)) {
            $this->logger->info('No migrations to reset');
            return;
        }

        $this->logger->info('Resetting all migrations');

        foreach (array_reverse($migrations) as $migration) {
            $this->rollbackMigration($migration);
        }

        $this->logger->info('Reset completed');
    }

    /**
     * Get migration status
     */
    public function status(): array
    {
        $migrations = $this->getAllMigrations();
        $ran = $this->getRanMigrations();

        $status = [];

        foreach ($migrations as $migration) {
            $status[] = [
                'migration' => $migration,
                'ran' => in_array($migration, $ran),
                'batch' => $this->getMigrationBatch($migration)
            ];
        }

        return $status;
    }

    /**
     * Run single migration
     */
    private function runMigration(string $migration): void
    {
        try {
            $this->logger->info("Running migration: {$migration}");

            $instance = $this->resolveMigration($migration);
            $instance->up();

            $this->recordMigration($migration);

            $this->logger->info("Migration completed: {$migration}");
        } catch (\Exception $e) {
            $this->logger->error("Migration failed: {$migration} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Rollback single migration
     */
    private function rollbackMigration(string $migration): void
    {
        try {
            $this->logger->info("Rolling back migration: {$migration}");

            $instance = $this->resolveMigration($migration);
            $instance->down();

            $this->removeMigration($migration);

            $this->logger->info("Migration rolled back: {$migration}");
        } catch (\Exception $e) {
            $this->logger->error("Rollback failed: {$migration} - " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Resolve migration instance
     */
    private function resolveMigration(string $migration): Migration
    {
        $file = $this->migrationsPath . '/' . $migration . '.php';

        if (!file_exists($file)) {
            throw new \Exception("Migration file not found: {$migration}");
        }

        require_once $file;

        $class = $this->getMigrationClass($migration);

        if (!class_exists($class)) {
            throw new \Exception("Migration class not found: {$class}");
        }

        return new $class();
    }

    /**
     * Get migration class name
     */
    private function getMigrationClass(string $migration): string
    {
        // Remove timestamp prefix (e.g., 20231201120000_)
        $migration = preg_replace('/^\d+_/', '', $migration);

        $parts = explode('_', $migration);
        $class = '';

        foreach ($parts as $part) {
            $class .= ucfirst($part);
        }

        return $class;
    }

    /**
     * Get all migration files
     */
    private function getAllMigrations(): array
    {
        if (!is_dir($this->migrationsPath)) {
            return [];
        }

        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];

        foreach ($files as $file) {
            $migrations[] = basename($file, '.php');
        }

        sort($migrations);
        return $migrations;
    }

    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array
    {
        $all = $this->getAllMigrations();
        $ran = $this->getRanMigrations();

        return array_diff($all, $ran);
    }

    /**
     * Get ran migrations
     */
    private function getRanMigrations(): array
    {
        $sql = "SELECT migration FROM migrations ORDER BY id";
        $statement = $this->connection->query($sql);
        $results = $statement->fetchAll(PDO::FETCH_COLUMN);

        return $results;
    }

    /**
     * Get last batch migrations
     */
    private function getLastBatchMigrations(): array
    {
        $sql = "SELECT migration FROM migrations WHERE batch = (SELECT MAX(batch) FROM migrations) ORDER BY id";
        $statement = $this->connection->query($sql);
        $results = $statement->fetchAll(PDO::FETCH_COLUMN);

        return $results;
    }

    /**
     * Get migration batch
     */
    private function getMigrationBatch(string $migration): ?int
    {
        $sql = "SELECT batch FROM migrations WHERE migration = ?";
        $statement = $this->connection->query($sql, [$migration]);
        $result = $statement->fetch(PDO::FETCH_COLUMN);

        return $result ? (int) $result : null;
    }

    /**
     * Record migration as ran
     */
    private function recordMigration(string $migration): void
    {
        $batch = $this->getNextBatchNumber();

        $sql = "INSERT INTO migrations (migration, batch, ran_at) VALUES (?, ?, datetime('now'))";
        $this->connection->query($sql, [$migration, $batch]);
    }

    /**
     * Remove migration record
     */
    private function removeMigration(string $migration): void
    {
        $sql = "DELETE FROM migrations WHERE migration = ?";
        $this->connection->query($sql, [$migration]);
    }

    /**
     * Get next batch number
     */
    private function getNextBatchNumber(): int
    {
        $sql = "SELECT MAX(batch) FROM migrations";
        $statement = $this->connection->query($sql);
        $result = $statement->fetch(PDO::FETCH_COLUMN);

        return $result ? (int) $result + 1 : 1;
    }

    /**
     * Ensure migrations table exists
     */
    private function ensureMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            batch INT NOT NULL,
            ran_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->query($sql);
    }
}
