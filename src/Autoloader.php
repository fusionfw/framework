<?php

namespace Fusion;

/**
 * Simple PSR-4 Autoloader untuk Flexify Framework
 */
class Autoloader
{
    private static $namespaces = [];
    private static $directories = [];

    /**
     * Register autoloader
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'load']);
    }

    /**
     * Add namespace mapping
     */
    public static function addNamespace(string $prefix, string $baseDir)
    {
        $prefix = trim($prefix, '\\') . '\\';
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        self::$namespaces[$prefix] = $baseDir;
    }

    /**
     * Add directory for class loading
     */
    public static function addDirectory(string $directory)
    {
        self::$directories[] = rtrim($directory, DIRECTORY_SEPARATOR) . '/';
    }

    /**
     * Load class
     */
    public static function load(string $class): bool
    {
        // Try namespace mapping first
        foreach (self::$namespaces as $prefix => $baseDir) {
            if (strpos($class, $prefix) === 0) {
                $relativeClass = substr($class, strlen($prefix));
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                }
            }
        }

        // Try directories
        foreach (self::$directories as $directory) {
            $file = $directory . str_replace('\\', '/', $class) . '.php';
            if (file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }
}
