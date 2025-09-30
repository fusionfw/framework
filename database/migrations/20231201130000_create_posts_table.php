<?php

use Fusion\Core\Database\Migration;

class CreatePostsTable extends Migration
{
    public function up(): void
    {
        $sql = "CREATE TABLE posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            slug VARCHAR(255) UNIQUE NOT NULL,
            excerpt TEXT NULL,
            featured_image VARCHAR(255) NULL,
            status VARCHAR(20) DEFAULT 'draft' CHECK (status IN ('draft', 'published')),
            author_id INTEGER NULL,
            published_at TIMESTAMP NULL,
            view_count INTEGER DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        $this->connection->query($sql);
    }

    public function down(): void
    {
        $sql = "DROP TABLE IF EXISTS posts";
        $this->connection->query($sql);
    }
}
