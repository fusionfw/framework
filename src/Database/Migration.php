<?php

namespace Fusion\Database;

use Fusion\\Database\Connection;
use Fusion\\Logger;

/**
 * Base Migration Class
 */
abstract class Migration
{
    protected $connection;
    protected $logger;

    public function __construct()
    {
        $this->connection = Connection::getInstance();
        $this->logger = \Fusion\Core\Container::getInstance()->make('logger');
    }

    /**
     * Run the migration
     */
    abstract public function up(): void;

    /**
     * Reverse the migration
     */
    abstract public function down(): void;

    /**
     * Get migration name
     */
    public function getName(): string
    {
        $reflection = new \ReflectionClass($this);
        return $reflection->getShortName();
    }

    /**
     * Get migration timestamp
     */
    public function getTimestamp(): string
    {
        $reflection = new \ReflectionClass($this);
        $filename = basename($reflection->getFileName());

        // Extract timestamp from filename (e.g., 20231201120000_create_users_table.php)
        if (preg_match('/^(\d{14})_/', $filename, $matches)) {
            return $matches[1];
        }

        return date('YmdHis');
    }
}
