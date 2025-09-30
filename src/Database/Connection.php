<?php

namespace Fusion\Database;

use PDO;
use PDOException;
use Fusion\\Config;
use Fusion\\Logger;

/**
 * Database Connection Manager
 */
class Connection
{
    private static $instances = [];
    private $pdo;
    private $config;
    private $logger;

    public function __construct(Config $config, Logger $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * Get database connection instance
     */
    public static function getInstance(string $connection = 'default'): self
    {
        if (!isset(self::$instances[$connection])) {
            $app = \Fusion\Core\Container::getInstance()->make(\Fusion\Core\Application::class);
            $config = $app->getConfig();
            $logger = $app->getLogger();

            self::$instances[$connection] = new self($config, $logger);
        }

        return self::$instances[$connection];
    }

    /**
     * Get PDO connection
     */
    public function getPdo(): PDO
    {
        if ($this->pdo === null) {
            $this->connect();
        }

        return $this->pdo;
    }

    /**
     * Connect to database
     */
    private function connect(): void
    {
        try {
            $defaultConnection = $this->config->get('database.default', 'mysql');
            $config = $this->config->get("database.connections.{$defaultConnection}", []);

            $dsn = $this->buildDsn($config);
            $options = $config['options'] ?? [];

            if ($defaultConnection === 'sqlite') {
                $this->pdo = new PDO($dsn, null, null, $options);
            } else {
                $this->pdo = new PDO($dsn, $config['username'], $config['password'], $options);
            }

            $this->logger->info('Database connected successfully');
        } catch (PDOException $e) {
            $this->logger->error('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Database connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Build DSN string
     */
    private function buildDsn(array $config): string
    {
        $driver = $config['driver'] ?? 'mysql';
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $database = $config['database'] ?? '';
        $charset = $config['charset'] ?? 'utf8mb4';

        if ($driver === 'sqlite') {
            return "sqlite:" . $config['database'];
        }

        return "{$driver}:host={$host};port={$port};dbname={$database};charset={$charset}";
    }

    /**
     * Execute query
     */
    public function query(string $sql, array $bindings = []): \PDOStatement
    {
        try {
            $statement = $this->getPdo()->prepare($sql);
            $statement->execute($bindings);

            $this->logger->debug('Query executed', [
                'sql' => $sql,
                'bindings' => $bindings
            ]);

            return $statement;
        } catch (PDOException $e) {
            $this->logger->error('Query failed: ' . $e->getMessage(), [
                'sql' => $sql,
                'bindings' => $bindings
            ]);
            throw new \Exception('Query failed: ' . $e->getMessage());
        }
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string
    {
        return $this->getPdo()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool
    {
        return $this->getPdo()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool
    {
        return $this->getPdo()->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool
    {
        return $this->getPdo()->rollback();
    }

    /**
     * Check if in transaction
     */
    public function inTransaction(): bool
    {
        return $this->getPdo()->inTransaction();
    }
}
