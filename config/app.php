<?php

return [
    'name' => 'Fusion Framework',
    'version' => '1.0.0',
    'description' => 'Fusion of Flexify + Flight (HMF) Framework',
    'debug' => true,
    'timezone' => 'Asia/Jakarta',
    'charset' => 'UTF-8',
    'log_path' => __DIR__ . '/../storage/logs',
    'cache_path' => __DIR__ . '/../storage/cache',

    // Session configuration (Enhanced from both frameworks)
    'session' => [
        'driver' => 'file',
        'lifetime' => 7200, // 2 hours (from Flight)
        'path' => '/',
        'domain' => null,
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
        'name' => 'FUSION_SESSION'
    ],

    // Security configuration (Enhanced from both frameworks)
    'security' => [
        'csrf_protection' => true,
        'xss_protection' => true,
        'password_hash_algo' => PASSWORD_DEFAULT,
        'session_secure' => false, // Set to true in production with HTTPS
        'session_httponly' => true,
        'session_lifetime' => 7200, // 2 hours
    ],

    // Logging configuration (Enhanced)
    'logging' => [
        'enabled' => true,
        'level' => 'info', // debug, info, warning, error
        'file' => __DIR__ . '/../storage/logs/app.log',
        'max_files' => 30,
    ],

    // Cache configuration (Enhanced)
    'cache' => [
        'enabled' => false,
        'driver' => 'file',
        'path' => __DIR__ . '/../storage/cache',
        'ttl' => 3600,
    ],

    // Modules configuration
    'modules' => [
        'Home',
        'User',
        // Add more modules here
    ],

    // Middleware configuration (Enhanced)
    'middleware' => [
        'global' => [
            'SecurityMiddleware',
            'CsrfMiddleware',
        ],
        'auth' => [
            'AuthMiddleware',
        ],
        'api' => [
            'ApiMiddleware',
            'RateLimitMiddleware',
        ],
    ],

    // Framework compatibility
    'compatibility' => [
        'flexify' => true,
        'hmf' => true,
        'flight' => true,
    ],

    // CLI configuration
    'cli' => [
        'default_command' => 'help',
        'hmf_compatibility' => true,
        'flexify_commands' => true,
    ],
];
