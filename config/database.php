<?php

return [
    'default' => $_ENV['DB_CONNECTION'] ?? 'mysql',
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '3306',
            'database' => $_ENV['DB_DATABASE'] ?? 'flexify',
            'username' => $_ENV['DB_USERNAME'] ?? 'root',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => $_ENV['DB_DATABASE'] ?? __DIR__ . '/../database.sqlite',
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'port' => $_ENV['DB_PORT'] ?? '5432',
            'database' => $_ENV['DB_DATABASE'] ?? 'fusion_framework',
            'username' => $_ENV['DB_USERNAME'] ?? 'postgres',
            'password' => $_ENV['DB_PASSWORD'] ?? '',
            'charset' => 'utf8',
            'options' => [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ],
        ],
    ],
];
