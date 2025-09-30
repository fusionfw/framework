<?php

use Fusion\Core\Database\Migration;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            remember_token VARCHAR(100) NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->query($sql);
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS users";
        $this->connection->query($sql);
    }
}
