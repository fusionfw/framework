<?php

/**
 * Fusion Framework Bootstrap
 * Fusion Framework Bootstrap - Independent with dual-mode runtime
 */

// Start session
session_start();

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Define base path
define('BASE_PATH', dirname(__DIR__));
define('FUSION_VERSION', '1.1.0');
define('FUSION_FRAMEWORK', 'Fusion Framework');

// Load autoloader
require_once __DIR__ . '/core/Autoloader.php';
require_once __DIR__ . '/core/FusionAliases.php';

// Register autoloader
use Fusion\Core\Autoloader;

Autoloader::register();

// Add namespace mappings
Autoloader::addNamespace('Fusion\\Core', __DIR__ . '/core');
Autoloader::addNamespace('App', __DIR__ . '/app');

// Load environment variables
if (file_exists(BASE_PATH . '/.env')) {
    $env = new \Fusion\Core\Config(__DIR__ . '/config');
    $env->loadEnv();
}

// Initialize Fusion Framework
try {
    // Load configuration
    $config = new \Fusion\Core\Config(__DIR__ . '/config');

    // Initialize container
    $container = \Fusion\Core\Container::getInstance();

    // Register core services
    $container->singleton('config', function () use ($config) {
        return $config;
    });

    $container->singleton('logger', function () use ($config) {
        return new \Fusion\Core\Logger($config->get('app.log_path', __DIR__ . '/../storage/logs'));
    });

    $container->singleton('cache', function () use ($config) {
        return new \Fusion\Core\Cache\CacheManager($config);
    });

    $container->singleton('session', function () use ($config) {
        return new \Fusion\Core\Session\SessionManager($config);
    });

    $container->singleton('auth', function () use ($config) {
        return new \Fusion\Core\Auth\AuthManager($config);
    });

    // Initialize database connection
    $dbConfig = $config->get('database', []);
    if (!empty($dbConfig)) {
        $container->singleton('database', function () use ($dbConfig) {
            return new \Fusion\Core\Database\Connection($dbConfig);
        });
    }

    // Initialize plugin manager (optional)
    try {
        $container->singleton('plugin_manager', function () use ($container, $config) {
            $logger = $container->make('logger');
            return new \Fusion\Core\Plugin\PluginManager($container, $logger, $config);
        });
    } catch (\Exception $e) {
        // Plugin manager not available, continue without it
        $logger = $container->make('logger');
        $logger->warning('Plugin manager not available: ' . $e->getMessage());
    }

    // Container is already set as singleton

    // Log framework initialization
    // Initialize dual-mode Application
    $app = \Fusion\Core\Application::getInstance();
    $mode = $app->getMode();

    $logger = $container->make('logger');
    $logger->info('Fusion Framework initialized successfully', [
        'version' => FUSION_VERSION,
        'framework' => FUSION_FRAMEWORK,
        'mode' => $mode,
        'php_version' => PHP_VERSION,
        'memory_limit' => ini_get('memory_limit')
    ]);
} catch (\Exception $e) {
    // Log initialization error
    error_log('Fusion Framework initialization failed: ' . $e->getMessage());

    // In development, show error
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "Fusion Framework initialization failed: " . $e->getMessage();
        exit(1);
    }
}
