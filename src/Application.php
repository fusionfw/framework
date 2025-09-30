<?php

namespace Fusion;

use Fusion\Container;
use Fusion\Config;
use Fusion\Logger;
use Fusion\Request;
use Fusion\Response;
use Fusion\Router;

class Application
{
    private static ?Application $instance = null;

    private string $mode; // lite | enterprise

    private Container $container;

    private Config $config;

    private function __construct()
    {
        $this->container = Container::getInstance();
        $this->config = new Config(dirname(__DIR__, 1) . '/config');
        $this->mode = getenv('APP_MODE') ?: 'lite';
        $this->mode = strtolower($this->mode) === 'enterprise' ? 'enterprise' : 'lite';
        $this->bootstrap();
    }

    public static function getInstance(): Application
    {
        if (!self::$instance) {
            self::$instance = new Application();
        }
        return self::$instance;
    }

    public static function boot(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function create(): self
    {
        return self::boot();
    }

    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getConfig(): Config
    {
        return $this->config;
    }

    public function getLogger(): Logger
    {
        return $this->container->make('logger');
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function run(): void
    {
        // Start the application
        $this->getLogger()->info('Application started');

        // Handle the request
        $request = new Request();

        // Process the request through router
        $router = new Router();
        
        // Add default routes
        $router->get('/', function () {
            $landingPath = dirname(__DIR__) . '/public/landing.html';
            if (is_file($landingPath)) {
                $content = file_get_contents($landingPath);
                $resp = new Response($content, 200);
                $resp->setHeader('Content-Type', 'text/html; charset=UTF-8');
                return $resp;
            }
            return new Response('<h1>Fusion Framework</h1><p>Landing page not found.</p>');
        });
        
        $router->get('/health', function () {
            return (new Response())->json(['status' => 'ok', 'framework' => 'Fusion', 'version' => '1.1.0']);
        });

        $response = $router->dispatch($request);

        // Send response
        $response->send();
    }

    private function bootstrap(): void
    {
        // Register Application instance
        $this->container->singleton(Application::class, function () {
            return $this;
        });

        // Register config
        $this->container->singleton('config', function () {
            return $this->config;
        });

        // Common services for both modes
        $this->container->singleton('logger', function () {
            return new Logger($this->config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));
        });

        // Register migrator service
        $this->container->singleton('migrator', function () {
            $connection = Database\Connection::getInstance() ?? throw new \RuntimeException('Database connection failed');
            $logger = $this->container->make('logger');
            return new Database\Migrator($connection, $logger);
        });

        // Register queue manager service
        $this->container->singleton('queue', function () {
            return new Queue\QueueManager($this->config);
        });

        // Minimal services for lite mode
        if ($this->mode === 'lite') {
            // Only minimal services
            $this->initDatabaseIfConfigured();
            return;
        }

        // Enterprise services
        $this->container->singleton('cache', function () {
            $logger = $this->container->make('logger');
            return new Cache\CacheManager($this->config, $logger);
        });

        $this->container->singleton('session', function () {
            return new Session\SessionManager($this->config);
        });

        $this->container->singleton('auth', function () {
            $session = $this->container->make('session');
            $cache = $this->container->make('cache');
            $logger = $this->container->make('logger');
            return new Auth\AuthManager($session, $cache, $logger);
        });

        $this->initDatabaseIfConfigured();

        // Plugin system is enterprise-only
        try {
            $this->container->singleton('plugin_manager', function () {
                $logger = $this->container->make('logger');
                return new Plugin\PluginManager($this->container, $logger, $this->config);
            });
        } catch (\Throwable $e) {
            $logger = $this->container->make('logger');
            $logger->warning('Plugin manager not available: ' . $e->getMessage());
        }
    }

    private function initDatabaseIfConfigured(): void
    {
        $dbConfig = $this->config->get('database', []);
        if (!empty($dbConfig)) {
            $this->container->singleton('database', function () {
                return Database\Connection::getInstance();
            });
        }
    }
}
