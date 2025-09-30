<?php

namespace Fusion\Core;

use Fusion\Core\Database\Migrator;
use Fusion\Core\Benchmark\BenchmarkRunner;
use Fusion\Core\Router;
use Fusion\Core\Model;
use Fusion\Core\Cache\ArrayCache;
use Fusion\Core\Service;
use Fusion\Core\Repository;
use Fusion\Core\Plugin\PluginManager;

/**
 * Simple CLI Console
 */
class Console
{
    private $commands = [];

    public function __construct()
    {
        // Commands will be registered when run() is called to support mode switching
    }

    /**
     * Register default commands
     */
    private function registerDefaultCommands()
    {
        // Determine mode from Application
        $mode = Application::getInstance()->getMode();

        // Common basic commands
        $this->addCommand('serve', function ($args) {
            $this->serve($args);
        });

        $this->addCommand('migrate', function ($args) {
            $this->migrate($args);
        });

        $this->addCommand('migrate:rollback', function ($args) {
            $this->migrateRollback($args);
        });

        $this->addCommand('migrate:reset', function ($args) {
            $this->migrateReset($args);
        });

        $this->addCommand('migrate:status', function ($args) {
            $this->migrateStatus($args);
        });

        // Queue commands
        $this->addCommand('queue:push', function ($args) {
            $this->queuePush($args);
        });

        $this->addCommand('queue:work', function ($args) {
            $this->queueWork($args);
        });

        $this->addCommand('queue:failed', function ($args) {
            $this->queueFailed($args);
        });

        $this->addCommand('queue:retry', function ($args) {
            $this->queueRetry($args);
        });

        $this->addCommand('queue:clear', function ($args) {
            $this->queueClear($args);
        });

        $this->addCommand('queue:drivers', function ($args) {
            $this->queueDrivers($args);
        });

        $this->addCommand('make:controller', function ($args) {
            $this->makeController($args);
        });

        $this->addCommand('make:model', function ($args) {
            $this->makeModel($args);
        });

        $this->addCommand('make:service', function ($args) {
            $this->makeService($args);
        });

        $this->addCommand('make:repository', function ($args) {
            $this->makeRepository($args);
        });

        $this->addCommand('make:middleware', function ($args) {
            $this->makeMiddleware($args);
        });

        $this->addCommand('make:module', function ($args) {
            $this->makeModule($args);
        });

        // Mode-enforced aliases
        $this->addCommand('lite', function ($args) {
            $this->runForcedMode('lite', $args);
        });
        $this->addCommand('enterprise', function ($args) {
            $this->runForcedMode('enterprise', $args);
        });

        $this->addCommand('new', function ($args) {
            $this->createProject($args);
        });

        if ($mode === 'enterprise') {
            $this->addCommand('plugin:list', function ($args) {
                $this->listPlugins($args);
            });

            $this->addCommand('plugin:install', function ($args) {
                $this->installPlugin($args);
            });

            $this->addCommand('plugin:uninstall', function ($args) {
                $this->uninstallPlugin($args);
            });

            $this->addCommand('plugin:activate', function ($args) {
                $this->activatePlugin($args);
            });

            $this->addCommand('plugin:deactivate', function ($args) {
                $this->deactivatePlugin($args);
            });
        }

        // New enhanced commands (enterprise only)
        if ($mode === 'enterprise') {
            $this->addCommand('key:generate', function ($args) {
                $this->keyGenerate($args);
            });

            $this->addCommand('route:list', function ($args) {
                $this->routeList($args);
            });

            $this->addCommand('cache:clear', function ($args) {
                $this->cacheClear($args);
            });

            $this->addCommand('config:cache', function ($args) {
                $this->configCache($args);
            });

            $this->addCommand('db:seed', function ($args) {
                $this->dbSeed($args);
            });

            $this->addCommand('optimize', function ($args) {
                $this->optimize($args);
            });

            $this->addCommand('make:seeder', function ($args) {
                $this->makeSeeder($args);
            });

            $this->addCommand('make:factory', function ($args) {
                $this->makeFactory($args);
            });

            $this->addCommand('make:request', function ($args) {
                $this->makeRequest($args);
            });

            $this->addCommand('make:job', function ($args) {
                $this->makeJob($args);
            });

            $this->addCommand('queue:work', function ($args) {
                $this->queueWork($args);
            });

            $this->addCommand('queue:restart', function ($args) {
                $this->queueRestart($args);
            });

            $this->addCommand('storage:link', function ($args) {
                $this->storageLink($args);
            });

            $this->addCommand('tinker', function ($args) {
                $this->tinker($args);
            });
        }

        // Remove legacy commands for independent Fusion
    }

    /**
     * Add command
     */
    public function addCommand(string $name, callable $callback): self
    {
        $this->commands[$name] = $callback;
        return $this;
    }

    /**
     * Run console
     */
    public function run(array $argv = null): void
    {
        $argv = $argv ?: $_SERVER['argv'];
        $command = $argv[1] ?? 'help';
        $args = array_slice($argv, 2);

        // Support prefix mode forcing: fusion lite <cmd> / fusion enterprise <cmd>
        if (in_array($command, ['lite', 'enterprise'])) {
            putenv('APP_MODE=' . $command);
            // Reset application singleton to force recreation with new mode
            Application::resetInstance();
            $command = $argv[2] ?? 'help';
            $args = array_slice($argv, 3);
        }

        // Register commands based on current mode
        $this->registerDefaultCommands();

        if ($command === 'help') {
            $this->showHelp();
            return;
        }

        if (isset($this->commands[$command])) {
            $this->commands[$command]($args);
        } else {
            echo "Command '{$command}' not found.\n";
            $this->showHelp();
        }
    }

    /**
     * Show help
     */
    private function showHelp(): void
    {
        $mode = Application::getInstance()->getMode();
        echo "Fusion Framework CLI ({$mode} mode)\n\n";
        echo "Available commands:\n\n";

        echo "  🚀 Development:\n";
        echo "    serve [host] [port]     Start development server\n";
        echo "    tinker                  Start interactive shell\n\n";

        echo "  🗄️  Database:\n";
        echo "    migrate                 Run database migrations\n";
        echo "    migrate:rollback        Rollback last migration\n";
        echo "    migrate:reset           Reset all migrations\n";
        echo "    migrate:status          Show migration status\n";
        echo "    db:seed                 Run database seeders\n\n";

        echo "  🏗️  Code Generation:\n";
        echo "    make:controller <name> [module]  Create controller\n";
        echo "    make:model <name> [module]       Create model\n";
        echo "    make:service <name> [module]     Create service\n";
        echo "    make:repository <name> [module]  Create repository\n";
        echo "    make:middleware <name>            Create middleware\n";
        echo "    make:module <name>                Create module\n";
        echo "    make:seeder <name>                Create seeder\n";
        echo "    make:factory <name>               Create factory\n";
        echo "    make:request <name>               Create form request\n";
        echo "    make:job <name>                   Create job\n\n";

        echo "  ⚙️  Configuration:\n";
        echo "    key:generate            Generate application key\n";
        echo "    config:cache            Cache configuration\n";
        echo "    cache:clear             Clear application cache\n";
        echo "    optimize                Optimize application\n\n";

        echo "  🛣️  Routing:\n";
        echo "    route:list              List all routes\n\n";

        echo "  📦 Queue:\n";
        echo "    queue:push <job> [data] [--driver=driver]  Push job to queue\n";
        echo "    queue:work [--driver=driver]               Start queue worker\n";
        echo "    queue:failed [--driver=driver]             Show failed jobs\n";
        echo "    queue:retry <job_id> [--driver=driver]     Retry failed job\n";
        echo "    queue:clear [--driver=driver]              Clear all jobs\n";
        echo "    queue:restart                              Restart queue workers\n";
        echo "    queue:drivers                              Show available drivers\n\n";

        echo "  💾 Storage:\n";
        echo "    storage:link            Create storage link\n\n";

        if ($mode === 'enterprise') {
            echo "  🔌 Plugin Management:\n";
            echo "    plugin:list             List installed plugins\n";
            echo "    plugin:install <name>   Install plugin\n";
            echo "    plugin:uninstall <name> Uninstall plugin\n";
            echo "    plugin:activate <name>  Activate plugin\n";
            echo "    plugin:deactivate <name> Deactivate plugin\n\n";
        }

        if ($mode === 'enterprise') {
            echo "  📊 Performance:\n";
            echo "    benchmark [url]         Run performance benchmarks\n\n";
        }

        echo "  🏗️  Project Management:\n";
        echo "    new <template> <name>   Create new project\n\n";
        echo "  Mode Forcing:\n";
        echo "    lite <command>          Force run command in lite mode\n";
        echo "    enterprise <command>    Force run command in enterprise mode\n\n";
    }

    private function runForcedMode(string $mode, array $args): void
    {
        putenv('APP_MODE=' . $mode);
        Application::resetInstance();
        $this->registerDefaultCommands();

        $cmd = $args[0] ?? 'help';
        $rest = array_slice($args, 1);
        if (isset($this->commands[$cmd])) {
            $this->commands[$cmd]($rest);
        } else {
            echo "Command '{$cmd}' not found.\n";
            $this->showHelp();
        }
    }

    /**
     * Serve command
     */
    private function serve(array $args): void
    {
        $host = $args[0] ?? 'localhost';
        $port = $args[1] ?? '8000';

        echo "Starting development server at http://{$host}:{$port}\n";
        echo "Press Ctrl+C to stop the server.\n\n";

        $command = "php -S {$host}:{$port} -t public";
        passthru($command);
    }

    /**
     * Migrate command
     */
    private function migrate(array $args): void
    {
        echo "Running migrations...\n";

        try {
            $app = Application::getInstance();
            $migrator = $app->getContainer()->make('migrator');
            $migrator->run();
            echo "Migrations completed.\n";
        } catch (\Exception $e) {
            echo "Migration failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Migrate rollback command
     */
    private function migrateRollback(array $args): void
    {
        echo "Rolling back migrations...\n";

        try {
            $app = Application::getInstance();
            $migrator = $app->getContainer()->make('migrator');
            $migrator->rollback();
            echo "Rollback completed.\n";
        } catch (\Exception $e) {
            echo "Rollback failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Migrate reset command
     */
    private function migrateReset(array $args): void
    {
        echo "Resetting all migrations...\n";

        try {
            $app = Application::getInstance();
            $migrator = $app->getContainer()->make('migrator');
            $migrator->reset();
            echo "Reset completed.\n";
        } catch (\Exception $e) {
            echo "Reset failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Migrate status command
     */
    private function migrateStatus(array $args): void
    {
        echo "Migration Status:\n";
        echo "================\n";

        try {
            $app = Application::getInstance();
            $migrator = $app->getContainer()->make('migrator');
            $status = $migrator->status();

            if (empty($status)) {
                echo "No migrations found.\n";
                return;
            }

            // Display as table
            $this->displayMigrationTable($status);
        } catch (\Exception $e) {
            echo "Status check failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Display migration status as table
     */
    private function displayMigrationTable(array $status): void
    {
        $maxNameLength = max(array_map('strlen', array_column($status, 'migration')));
        $maxNameLength = max($maxNameLength, 20); // Minimum width

        echo str_repeat("=", $maxNameLength + 20) . "\n";
        echo sprintf("%-{$maxNameLength}s %-10s %-8s\n", "Migration", "Status", "Batch");
        echo str_repeat("=", $maxNameLength + 20) . "\n";

        foreach ($status as $migration) {
            $statusText = $migration['ran'] ? '✓ Ran' : '✗ Pending';
            $batch = $migration['ran'] ? $migration['batch'] : '-';
            echo sprintf(
                "%-{$maxNameLength}s %-10s %-8s\n",
                $migration['migration'],
                $statusText,
                $batch
            );
        }
        echo str_repeat("=", $maxNameLength + 20) . "\n";
    }

    /**
     * Queue push command
     */
    private function queuePush(array $args): void
    {
        if (empty($args)) {
            echo "Usage: queue:push <JobClass> [data] [--driver=driver_name]\n";
            echo "Example: queue:push SendEmailJob '{\"email\":\"user@example.com\"}'\n";
            echo "Example: queue:push SendEmailJob '{\"email\":\"user@example.com\"}' --driver=redis\n";
            return;
        }

        $jobClass = $args[0];
        $data = [];
        $driver = null;

        // Parse arguments
        for ($i = 1; $i < count($args); $i++) {
            if (strpos($args[$i], '--driver=') === 0) {
                $driver = substr($args[$i], 9);
            } else {
                $data = json_decode($args[$i], true) ?: [];
            }
        }

        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            $result = $queueManager->push($jobClass, $data, 0, $driver);

            if ($result) {
                $driverText = $driver ? " using {$driver} driver" : "";
                echo "Job {$jobClass} pushed to queue successfully{$driverText}.\n";
            } else {
                echo "Failed to push job {$jobClass} to queue.\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Queue work command
     */
    private function queueWork(array $args): void
    {
        $driver = null;

        // Parse arguments
        foreach ($args as $arg) {
            if (strpos($arg, '--driver=') === 0) {
                $driver = substr($arg, 9);
            }
        }

        $driverText = $driver ? " using {$driver} driver" : "";
        echo "Starting queue worker{$driverText}...\n";
        echo "Press Ctrl+C to stop.\n\n";

        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            while (true) {
                $job = $queueManager->pop($driver);

                if ($job === null) {
                    sleep(1);
                    continue;
                }

                echo "Processing job: {$job['job']} (ID: {$job['id']})\n";

                try {
                    if (!class_exists($job['job'])) {
                        throw new \Exception("Job class {$job['job']} not found");
                    }

                    if (!is_subclass_of($job['job'], \Fusion\Core\Queue\Job::class)) {
                        throw new \Exception("Job class {$job['job']} must extend " . \Fusion\Core\Queue\Job::class);
                    }

                    $jobInstance = new $job['job']($job['data']);
                    $jobInstance->handle();

                    // Acknowledge job completion
                    $queueManager->ack($job['id'], $driver);
                    echo "Job completed successfully.\n\n";
                } catch (\Throwable $e) {
                    echo "Job failed: " . $e->getMessage() . "\n";
                    $queueManager->fail($job['id'], $e->getMessage(), $driver);
                    echo "Job marked as failed.\n\n";
                }
            }
        } catch (\Exception $e) {
            echo "Worker error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Queue failed command
     */
    private function queueFailed(array $args): void
    {
        $driver = null;

        // Parse arguments
        foreach ($args as $arg) {
            if (strpos($arg, '--driver=') === 0) {
                $driver = substr($arg, 9);
            }
        }

        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            $failedJobs = $queueManager->getFailed($driver);

            if (empty($failedJobs)) {
                $driverText = $driver ? " for {$driver} driver" : "";
                echo "No failed jobs found{$driverText}.\n";
                return;
            }

            $driverText = $driver ? " ({$driver} driver)" : "";
            echo "Failed Jobs{$driverText}:\n";
            echo "============\n";

            foreach ($failedJobs as $job) {
                echo "ID: {$job['id']}\n";
                echo "Job: " . ($job['job'] ?? 'Unknown') . "\n";
                echo "Failed at: " . date('Y-m-d H:i:s', $job['failed_at']) . "\n";
                echo "Error: {$job['error']}\n";
                echo "Data: " . json_encode($job['data'] ?? [], JSON_PRETTY_PRINT) . "\n";
                echo "---\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Queue retry command
     */
    private function queueRetry(array $args): void
    {
        if (empty($args)) {
            echo "Usage: queue:retry <JobID> [--driver=driver_name]\n";
            return;
        }

        $jobId = $args[0];
        $driver = null;

        // Parse arguments
        for ($i = 1; $i < count($args); $i++) {
            if (strpos($args[$i], '--driver=') === 0) {
                $driver = substr($args[$i], 9);
            }
        }

        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            $result = $queueManager->retry($jobId, $driver);

            if ($result) {
                $driverText = $driver ? " using {$driver} driver" : "";
                echo "Job {$jobId} retried successfully{$driverText}.\n";
            } else {
                echo "Failed to retry job {$jobId}.\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Queue clear command
     */
    private function queueClear(array $args): void
    {
        $driver = null;

        // Parse arguments
        foreach ($args as $arg) {
            if (strpos($arg, '--driver=') === 0) {
                $driver = substr($arg, 9);
            }
        }

        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            $result = $queueManager->clear($driver);

            if ($result) {
                $driverText = $driver ? " for {$driver} driver" : "";
                echo "Queue cleared successfully{$driverText}.\n";
            } else {
                echo "Failed to clear queue.\n";
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Queue drivers command
     */
    private function queueDrivers(array $args): void
    {
        try {
            $app = Application::getInstance();
            $queueManager = $app->getContainer()->make('queue');

            $drivers = $queueManager->getAvailableDrivers();
            $defaultDriver = $app->getContainer()->make('config')->get('queue.default', 'sync');

            echo "Available Queue Drivers:\n";
            echo "======================\n\n";

            foreach ($drivers as $driver) {
                $status = ($driver === $defaultDriver) ? " (default)" : "";
                echo "• {$driver}{$status}\n";
            }

            echo "\nCurrent default driver: {$defaultDriver}\n";
            echo "\nUsage: php fusion queue:push JobClass --driver=driver_name\n";
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Make controller command
     */
    private function makeController(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:controller <ControllerName>\n";
            return;
        }

        $name = $args[0];
        $module = $args[1] ?? 'App';

        $this->generateController($name, $module);
    }

    /**
     * Make model command
     */
    private function makeModel(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:model <ModelName>\n";
            return;
        }

        $name = $args[0];
        $module = $args[1] ?? 'App';

        $this->generateModel($name, $module);
    }

    /**
     * Make service command
     */
    private function makeService(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:service <ServiceName>\n";
            return;
        }

        $name = $args[0];
        $module = $args[1] ?? 'App';

        $this->generateService($name, $module);
    }

    /**
     * Make middleware command
     */
    private function makeMiddleware(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:middleware <MiddlewareName>\n";
            return;
        }

        $name = $args[0];
        $this->generateMiddleware($name);
    }

    /**
     * Generate controller file
     */
    private function generateController(string $name, string $module): void
    {
        $modulePath = "app/modules/{$module}/Controllers";
        if (!is_dir($modulePath)) {
            mkdir($modulePath, 0755, true);
        }

        $content = "<?php\n\nnamespace App\\Modules\\{$module}\\Controllers;\n\nuse Fusion\\Core\\Controller;\nuse Fusion\\Core\\Request;\nuse Fusion\\Core\\Response;\n\nclass {$name} extends Controller\n{\n    public function index(Request \$request): Response\n    {\n        return \$this->view('{$module}.{$name}.index');\n    }\n}\n";

        file_put_contents("{$modulePath}/{$name}.php", $content);
        echo "Controller created: {$modulePath}/{$name}.php\n";
    }

    /**
     * Generate model file
     */
    private function generateModel(string $name, string $module): void
    {
        $modulePath = "app/modules/{$module}/Models";
        if (!is_dir($modulePath)) {
            mkdir($modulePath, 0755, true);
        }

        $tableName = strtolower($name) . 's';
        $content = "<?php\n\nnamespace App\\Modules\\{$module}\\Models;\n\nuse Fusion\\Core\\Model;\n\nclass {$name} extends Model\n{\n    protected \$table = '{$tableName}';\n    protected \$fillable = [];\n}\n";

        file_put_contents("{$modulePath}/{$name}.php", $content);
        echo "Model created: {$modulePath}/{$name}.php\n";
    }

    /**
     * Generate service file
     */
    private function generateService(string $name, string $module): void
    {
        $modulePath = "app/modules/{$module}/Services";
        if (!is_dir($modulePath)) {
            mkdir($modulePath, 0755, true);
        }

        $content = "<?php\n\nnamespace App\\Modules\\{$module}\\Services;\n\nuse Fusion\\Core\\Service;\n\nclass {$name} extends Service\n{\n    // Add your business logic here\n}\n";

        file_put_contents("{$modulePath}/{$name}.php", $content);
        echo "Service created: {$modulePath}/{$name}.php\n";
    }

    /**
     * Generate middleware file
     */
    private function generateMiddleware(string $name): void
    {
        $middlewarePath = "app/Middleware";
        if (!is_dir($middlewarePath)) {
            mkdir($middlewarePath, 0755, true);
        }

        $content = "<?php\n\nnamespace App\\Middleware;\n\nuse Fusion\\Core\\Middleware;\nuse Fusion\\Core\\Request;\nuse Fusion\\Core\\Response;\n\nclass {$name} extends Middleware\n{\n    public function handle(Request \$request): ?Response\n    {\n        // Add your middleware logic here\n        return null;\n    }\n}\n";

        file_put_contents("{$middlewarePath}/{$name}.php", $content);
        echo "Middleware created: {$middlewarePath}/{$name}.php\n";
    }

    /**
     * Benchmark command
     */
    private function benchmark(array $args): void
    {
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));
        $benchmark = new BenchmarkRunner($logger);

        echo "🚀 Fusion Framework Performance Benchmark\n";
        echo str_repeat("=", 50) . "\n\n";

        // Test 1: Basic routing performance
        echo "Testing Basic Routing...\n";
        $benchmark->run('Basic Routing', function () {
            $router = new Router();
            $router->get('/test', function () {
                return 'Hello';
            });
            $request = \Fusion\Core\Request::createFromGlobals();
            $router->dispatch($request);
        }, 10000);

        // Test 2: Model operations
        echo "Testing Model Operations...\n";
        $benchmark->run('Model Operations', function () {
            $model = new class extends Model {
                protected $table = 'test';
                protected $fillable = ['name', 'email'];
            };
            // Simulate model operations without calling undefined methods
            $model->name = 'Test';
            $model->email = 'test@example.com';
        }, 5000);

        // Test 3: Cache operations
        echo "Testing Cache Operations...\n";
        $benchmark->run('Cache Operations', function () {
            $cache = new ArrayCache();
            $cache->set('test', 'value', 60);
            $cache->get('test');
            $cache->forget('test');
        }, 10000);

        // Test 4: Security operations
        echo "Testing Security Operations...\n";
        $benchmark->run('Security Operations', function () {
            \Fusion\Core\Security::hashPassword('password123');
            \Fusion\Core\Security::validateEmail('test@example.com');
            \Fusion\Core\Security::sanitizeInput('<script>alert("xss")</script>');
        }, 5000);

        // Test 5: Service operations
        echo "Testing Service Operations...\n";
        $benchmark->run('Service Operations', function () {
            $service = new class extends Service {
                public function test()
                {
                    return 'test';
                }
            };
            $service->test();
        }, 5000);

        // Test 6: Repository operations
        echo "Testing Repository Operations...\n";
        $benchmark->run('Repository Operations', function () {
            $repository = new class extends Repository {
                protected $table = 'test';

                public function testOperation()
                {
                    return $this->table;
                }
            };
            // Simulate repository operations
            $repository->testOperation();
        }, 3000);

        // Test 7: Memory usage
        echo "Testing Memory Usage...\n";
        $benchmark->run('Memory Operations', function () {
            $data = [];
            for ($i = 0; $i < 1000; $i++) {
                $data[] = [
                    'id' => $i,
                    'name' => 'Test ' . $i,
                    'email' => 'test' . $i . '@example.com',
                    'created_at' => date('Y-m-d H:i:s')
                ];
            }
            unset($data);
        }, 1000);

        // HTTP benchmark if URL provided
        if (!empty($args[0])) {
            $url = $args[0];
            $concurrency = (int)($args[1] ?? 10);
            $requests = (int)($args[2] ?? 100);
            echo "Testing HTTP Performance...\n";
            $benchmark->runHttp($url, $concurrency, $requests);
        }

        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 BENCHMARK RESULTS\n";
        echo str_repeat("=", 50) . "\n\n";
        echo $benchmark->generateReport();

        echo "\n💡 Performance Tips:\n";
        echo "• Use cache for frequently accessed data\n";
        echo "• Optimize database queries with proper indexing\n";
        echo "• Enable OPcache for better PHP performance\n";
        echo "• Use CDN for static assets\n";
        echo "• Consider using Redis for session storage\n\n";
    }

    /**
     * Create new project command
     */
    private function createProject(array $args): void
    {
        if (empty($args)) {
            echo "Usage: fusion new <template> <project-name>\n";
            echo "Available templates: blog, api, empty\n";
            return;
        }

        $template = $args[0];
        $projectName = $args[1] ?? 'fusion-app';
        $projectPath = getcwd() . '/' . $projectName;

        if (is_dir($projectPath)) {
            echo "Directory {$projectName} already exists!\n";
            return;
        }

        echo "Creating new Fusion project: {$projectName}\n";
        echo "Template: {$template}\n\n";

        // Create project directory
        mkdir($projectPath, 0755, true);

        // Copy base framework files
        $this->copyFrameworkFiles($projectPath);

        // Generate template-specific files
        switch ($template) {
            case 'blog':
                $this->generateBlogTemplate($projectPath);
                break;
            case 'api':
                $this->generateApiTemplate($projectPath);
                break;
            case 'empty':
            default:
                $this->generateEmptyTemplate($projectPath);
                break;
        }

        echo "Project created successfully!\n";
        echo "Next steps:\n";
        echo "1. cd {$projectName}\n";
        echo "2. composer install\n";
        echo "3. cp .env.example .env\n";
        echo "4. ./fusion migrate\n";
        echo "5. ./fusion serve\n";
    }

    /**
     * Copy framework files to project
     */
    private function copyFrameworkFiles(string $projectPath): void
    {
        $frameworkPath = dirname(__DIR__, 2);

        // Copy core files
        $this->copyDirectory($frameworkPath . '/core', $projectPath . '/core');
        $this->copyDirectory($frameworkPath . '/config', $projectPath . '/config');
        $this->copyDirectory($frameworkPath . '/public', $projectPath . '/public');
        $this->copyDirectory($frameworkPath . '/app', $projectPath . '/app');
        $this->copyDirectory($frameworkPath . '/tests', $projectPath . '/tests');
        $this->copyDirectory($frameworkPath . '/database', $projectPath . '/database');
        $this->copyDirectory($frameworkPath . '/storage', $projectPath . '/storage');
        $this->copyDirectory($frameworkPath . '/plugins', $projectPath . '/plugins');

        // Copy individual files
        if (file_exists($frameworkPath . '/bootstrap.php')) {
            copy($frameworkPath . '/bootstrap.php', $projectPath . '/bootstrap.php');
        }
        if (file_exists($frameworkPath . '/fusion')) {
            copy($frameworkPath . '/fusion', $projectPath . '/fusion');
        }
        if (file_exists($frameworkPath . '/composer.json')) {
            copy($frameworkPath . '/composer.json', $projectPath . '/composer.json');
        }
        if (file_exists($frameworkPath . '/phpunit.xml')) {
            copy($frameworkPath . '/phpunit.xml', $projectPath . '/phpunit.xml');
        }
        if (file_exists($frameworkPath . '/.gitignore')) {
            copy($frameworkPath . '/.gitignore', $projectPath . '/.gitignore');
        }
        if (file_exists($frameworkPath . '/.env.example')) {
            copy($frameworkPath . '/.env.example', $projectPath . '/.env.example');
        }
        if (file_exists($frameworkPath . '/README.md')) {
            copy($frameworkPath . '/README.md', $projectPath . '/README.md');
        }

        // Make fusion executable
        if (file_exists($projectPath . '/fusion')) {
            chmod($projectPath . '/fusion', 0755);
        }
    }

    /**
     * Copy directory recursively
     */
    private function copyDirectory(string $src, string $dst): void
    {
        if (!is_dir($src)) return;

        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        $files = scandir($src);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;

            if (is_dir($srcFile)) {
                $this->copyDirectory($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
    }

    /**
     * Generate blog template
     */
    private function generateBlogTemplate(string $projectPath): void
    {
        echo "Generating blog template...\n";

        // Create blog module
        $blogPath = $projectPath . '/app/modules/Blog';
        mkdir($blogPath . '/Controllers', 0755, true);
        mkdir($blogPath . '/Models', 0755, true);
        mkdir($blogPath . '/Services', 0755, true);
        mkdir($blogPath . '/Views', 0755, true);

        // Blog Controller
        $blogController = '<?php

namespace App\Modules\Blog\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $posts = $this->service(\'BlogService\')->getAllPosts();
        return $this->view(\'Blog.blog.index\', [\'posts\' => $posts]);
    }

    public function show(Request $request): Response
    {
        $id = $request->get(\'id\');
        $post = $this->service(\'BlogService\')->getPost($id);
        
        if (!$post) {
            return $this->error(\'Post not found\', 404);
        }
        
        return $this->view(\'Blog.blog.show\', [\'post\' => $post]);
    }
}';

        file_put_contents($blogPath . '/Controllers/BlogController.php', $blogController);

        // Blog Model
        $blogModel = '<?php

namespace App\Modules\Blog\Models;

use Fusion\Core\Model;

class Post extends Model
{
    protected $table = \'posts\';
    protected $fillable = [\'title\', \'content\', \'slug\', \'published\'];
    
    public function getExcerptAttribute()
    {
        return substr(strip_tags($this->content), 0, 150) . \'...\';
    }
}';

        file_put_contents($blogPath . '/Models/Post.php', $blogModel);

        // Blog Service
        $blogService = '<?php

namespace App\Modules\Blog\Services;

use Fusion\Core\Service;

class BlogService extends Service
{
    public function getAllPosts()
    {
        return $this->repository(\'PostRepository\')->all();
    }

    public function getPost($id)
    {
        return $this->repository(\'PostRepository\')->find($id);
    }
}';

        file_put_contents($blogPath . '/Services/BlogService.php', $blogService);

        // Blog routes
        $blogRoutes = '<?php

use Fusion\Core\Router;

$router = new Router();

$router->get(\'/\', \'Blog\Controllers\BlogController@index\');
$router->get(\'/post/{id}\', \'Blog\Controllers\BlogController@show\');';

        file_put_contents($blogPath . '/routes.php', $blogRoutes);

        // Blog views
        mkdir($blogPath . '/Views/blog', 0755, true);

        $indexView = '<!DOCTYPE html>
<html>
<head>
    <title>My Blog</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .post { border-bottom: 1px solid #eee; padding: 20px 0; }
        .post h2 { margin: 0 0 10px 0; }
        .post .excerpt { color: #666; }
    </style>
</head>
<body>
    <h1>My Blog</h1>
    <?php foreach ($posts as $post): ?>
        <div class="post">
            <h2><a href="/post/<?= $post->id ?>"><?= htmlspecialchars($post->title) ?></a></h2>
            <div class="excerpt"><?= htmlspecialchars($post->excerpt) ?></div>
        </div>
    <?php endforeach; ?>
</body>
</html>';

        file_put_contents($blogPath . '/Views/blog/index.php', $indexView);
    }

    /**
     * Generate API template
     */
    private function generateApiTemplate(string $projectPath): void
    {
        echo "Generating API template...\n";

        // Create API module
        $apiPath = $projectPath . '/app/modules/Api';
        mkdir($apiPath . '/Controllers', 0755, true);
        mkdir($apiPath . '/Models', 0755, true);
        mkdir($apiPath . '/Services', 0755, true);

        // API Controller
        $apiController = '<?php

namespace App\Modules\Api\Controllers;

use Fusion\Core\Controller;
use Fusion\Core\Request;
use Fusion\Core\Response;

class ApiController extends Controller
{
    public function index(Request $request): Response
    {
        return $this->json([
            \'message\' => \'Welcome to Fusion API\',
            \'version\' => \'1.0.0\',
            \'timestamp\' => date(\'c\')
        ]);
    }

    public function users(Request $request): Response
    {
        $users = $this->service(\'UserService\')->getAllUsers();
        return $this->json([\'data\' => $users]);
    }

    public function createUser(Request $request): Response
    {
        $data = $request->input();
        $user = $this->service(\'UserService\')->createUser($data);
        
        if ($user) {
            return $this->success($user, \'User created successfully\', 201);
        }
        
        return $this->error(\'Failed to create user\', 400);
    }
}';

        file_put_contents($apiPath . '/Controllers/ApiController.php', $apiController);

        // API routes
        $apiRoutes = '<?php

use Fusion\Core\Router;

$router = new Router();

$router->group([\'prefix\' => \'/api\'], function($router) {
    $router->get(\'/\', \'Api\Controllers\ApiController@index\');
    $router->get(\'/users\', \'Api\Controllers\ApiController@users\');
    $router->post(\'/users\', \'Api\Controllers\ApiController@createUser\');
});';

        file_put_contents($apiPath . '/routes.php', $apiRoutes);
    }

    /**
     * Generate empty template
     */
    private function generateEmptyTemplate(string $projectPath): void
    {
        echo "Generating empty template...\n";
        // Empty template - just the base framework
    }

    /**
     * List plugins command
     */
    private function listPlugins(array $args): void
    {
        $container = \Fusion\Core\Container::getInstance();
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));

        $pluginManager = new PluginManager($container, $logger, $config);
        $plugins = $pluginManager->getPlugins();

        echo "Installed Plugins:\n";
        echo str_repeat("=", 50) . "\n";

        if (empty($plugins)) {
            echo "No plugins installed.\n";
            return;
        }

        foreach ($plugins as $name => $plugin) {
            $status = $pluginManager->getStatus($name);
            echo "Name: {$status['name']}\n";
            echo "Version: {$status['version']}\n";
            echo "Description: {$status['description']}\n";
            echo "Compatible: " . ($status['compatible'] ? 'Yes' : 'No') . "\n";
            echo str_repeat("-", 30) . "\n";
        }
    }

    /**
     * Install plugin command
     */
    private function installPlugin(array $args): void
    {
        if (empty($args)) {
            echo "Usage: fusion plugin:install <plugin-name>\n";
            return;
        }

        $pluginName = $args[0];

        $container = \Fusion\Core\Container::getInstance();
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));

        $pluginManager = new PluginManager($container, $logger, $config);

        echo "Installing plugin: {$pluginName}\n";

        if ($pluginManager->install($pluginName)) {
            echo "Plugin installed successfully!\n";
        } else {
            echo "Plugin installation failed!\n";
        }
    }

    /**
     * Uninstall plugin command
     */
    private function uninstallPlugin(array $args): void
    {
        if (empty($args)) {
            echo "Usage: fusion plugin:uninstall <plugin-name>\n";
            return;
        }

        $pluginName = $args[0];

        $container = \Fusion\Core\Container::getInstance();
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));

        $pluginManager = new PluginManager($container, $logger, $config);

        echo "Uninstalling plugin: {$pluginName}\n";

        if ($pluginManager->uninstall($pluginName)) {
            echo "Plugin uninstalled successfully!\n";
        } else {
            echo "Plugin uninstallation failed!\n";
        }
    }

    /**
     * Activate plugin command
     */
    private function activatePlugin(array $args): void
    {
        if (empty($args)) {
            echo "Usage: fusion plugin:activate <plugin-name>\n";
            return;
        }

        $pluginName = $args[0];

        $container = \Fusion\Core\Container::getInstance();
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));

        $pluginManager = new PluginManager($container, $logger, $config);

        echo "Activating plugin: {$pluginName}\n";

        if ($pluginManager->activate($pluginName)) {
            echo "Plugin activated successfully!\n";
        } else {
            echo "Plugin activation failed!\n";
        }
    }

    /**
     * Deactivate plugin command
     */
    private function deactivatePlugin(array $args): void
    {
        if (empty($args)) {
            echo "Usage: fusion plugin:deactivate <plugin-name>\n";
            return;
        }

        $pluginName = $args[0];

        $container = \Fusion\Core\Container::getInstance();
        $config = new \Fusion\Core\Config(dirname(__DIR__, 2) . '/config');
        $logger = new \Fusion\Core\Logger($config->get('app.log_path', dirname(__DIR__, 2) . '/storage/logs'));

        $pluginManager = new PluginManager($container, $logger, $config);

        echo "Deactivating plugin: {$pluginName}\n";

        if ($pluginManager->deactivate($pluginName)) {
            echo "Plugin deactivated successfully!\n";
        } else {
            echo "Plugin deactivation failed!\n";
        }
    }

    /**
     * Legacy compatibility command
     */
    private function legacyCommand(string $legacyType, array $args): void
    {
        echo "⚠️  CLI '{$legacyType}' sudah deprecated, gunakan 'fusion' sebagai gantinya.\n";
        echo "⚠️  '{$legacyType}' akan dihapus di versi 2.0.\n\n";

        if (empty($args)) {
            echo "Legacy {$legacyType} Commands (Deprecated):\n";
            echo "  {$legacyType} serve [host] [port]     Start development server\n";
            echo "  {$legacyType} migrate                 Run database migrations\n";
            echo "  {$legacyType} make:controller <name> [module]  Create controller\n";
            echo "  {$legacyType} make:model <name> [module]       Create model\n";
            echo "  {$legacyType} make:service <name> [module]     Create service\n";
            echo "  {$legacyType} make:repository <name> [module]  Create repository\n";
            echo "  {$legacyType} make:middleware <name>            Create middleware\n";
            echo "  {$legacyType} make:module <name>                Create module\n";
            echo "\nGunakan 'fusion' sebagai gantinya.\n";
            return;
        }

        $command = $args[0];
        $commandArgs = array_slice($args, 1);

        // Map legacy commands to current commands
        switch ($command) {
            case 'serve':
                $this->serve($commandArgs);
                break;
            case 'migrate':
                $this->migrate($commandArgs);
                break;
            case 'make:controller':
                $this->makeController($commandArgs);
                break;
            case 'make:model':
                $this->makeModel($commandArgs);
                break;
            case 'make:service':
                $this->makeService($commandArgs);
                break;
            case 'make:repository':
                $this->makeRepository($commandArgs);
                break;
            case 'make:middleware':
                $this->makeMiddleware($commandArgs);
                break;
            case 'make:module':
                $this->makeModule($commandArgs);
                break;
            default:
                echo "Legacy {$legacyType} command '{$command}' not found.\n";
                echo "Gunakan 'fusion' sebagai gantinya.\n";
                break;
        }
    }

    /**
     * Make repository command
     */
    private function makeRepository(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:repository <RepositoryName> [ModuleName]\n";
            return;
        }

        $name = $args[0];
        $module = $args[1] ?? 'App';

        $this->generateRepository($name, $module);
    }

    /**
     * Make module command
     */
    private function makeModule(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:module <ModuleName>\n";
            return;
        }

        $name = $args[0];
        $this->generateModule($name);
    }

    /**
     * Generate repository file
     */
    private function generateRepository(string $name, string $module): void
    {
        $modulePath = "app/modules/{$module}/Repositories";
        if (!is_dir($modulePath)) {
            mkdir($modulePath, 0755, true);
        }

        $tableName = strtolower($name);
        $tableName = str_replace('Repository', '', $tableName);
        $tableName = strtolower($tableName) . 's';

        $content = "<?php\n\nnamespace App\\Modules\\{$module}\\Repositories;\n\nuse Fusion\\Core\\Repository;\n\nclass {$name} extends Repository\n{\n    protected \$table = '{$tableName}';\n    protected \$primaryKey = 'id';\n    \n    // Add your custom repository methods here\n}\n";

        file_put_contents("{$modulePath}/{$name}.php", $content);
        echo "Repository created: {$modulePath}/{$name}.php\n";
    }

    /**
     * Generate module structure
     */
    private function generateModule(string $name): void
    {
        $modulePath = "app/modules/{$name}";

        // Create module directories
        $directories = ['Controllers', 'Models', 'Services', 'Repositories', 'Views'];
        foreach ($directories as $dir) {
            $dirPath = "{$modulePath}/{$dir}";
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
        }

        // Create routes file
        $routesContent = "<?php\n\nuse Fusion\\Core\\Router;\n\n\$router = new Router();\n\n// Add your routes here\n";
        file_put_contents("{$modulePath}/routes.php", $routesContent);

        // Create sample controller
        $controllerContent = "<?php\n\nnamespace App\\Modules\\{$name}\\Controllers;\n\nuse Fusion\\Core\\Controller;\nuse Fusion\\Core\\Request;\nuse Fusion\\Core\\Response;\n\nclass {$name}Controller extends Controller\n{\n    public function index(Request \$request): Response\n    {\n        return \$this->view('{$name}.{$name}.index');\n    }\n}\n";
        file_put_contents("{$modulePath}/Controllers/{$name}Controller.php", $controllerContent);

        // Create sample model
        $modelContent = "<?php\n\nnamespace App\\Modules\\{$name}\\Models;\n\nuse Fusion\\Core\\Model;\n\nclass {$name} extends Model\n{\n    protected \$table = '" . strtolower($name) . "s';\n    protected \$fillable = [];\n}\n";
        file_put_contents("{$modulePath}/Models/{$name}.php", $modelContent);

        // Create sample service
        $serviceContent = "<?php\n\nnamespace App\\Modules\\{$name}\\Services;\n\nuse Fusion\\Core\\Service;\n\nclass {$name}Service extends Service\n{\n    // Add your business logic here\n}\n";
        file_put_contents("{$modulePath}/Services/{$name}Service.php", $serviceContent);

        // Create sample repository
        $repositoryContent = "<?php\n\nnamespace App\\Modules\\{$name}\\Repositories;\n\nuse Fusion\\Core\\Repository;\n\nclass {$name}Repository extends Repository\n{\n    protected \$table = '" . strtolower($name) . "s';\n    protected \$primaryKey = 'id';\n}\n";
        file_put_contents("{$modulePath}/Repositories/{$name}Repository.php", $repositoryContent);

        // Create sample view
        $viewDir = "{$modulePath}/Views/{$name}";
        if (!is_dir($viewDir)) {
            mkdir($viewDir, 0755, true);
        }

        $viewContent = "<!DOCTYPE html>\n<html>\n<head>\n    <title>{$name} Module</title>\n    <style>\n        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }\n    </style>\n</head>\n<body>\n    <h1>Welcome to {$name} Module</h1>\n    <p>This is a sample view for the {$name} module.</p>\n</body>\n</html>";
        file_put_contents("{$viewDir}/index.php", $viewContent);

        echo "Module created: {$modulePath}/\n";
        echo "  - Controllers/{$name}Controller.php\n";
        echo "  - Models/{$name}.php\n";
        echo "  - Services/{$name}Service.php\n";
        echo "  - Repositories/{$name}Repository.php\n";
        echo "  - Views/{$name}/index.php\n";
        echo "  - routes.php\n";
    }

    /**
     * Generate application key
     */
    private function keyGenerate(array $args): void
    {
        $key = bin2hex(random_bytes(32));
        $envFile = '.env';

        if (!file_exists($envFile)) {
            echo "⚠️  .env file not found. Please copy .env.example to .env first.\n";
            return;
        }

        $envContent = file_get_contents($envFile);
        $envContent = preg_replace('/^APP_KEY=.*/m', "APP_KEY={$key}", $envContent);

        if (file_put_contents($envFile, $envContent)) {
            echo "✅ Application key generated successfully!\n";
            echo "Key: {$key}\n";
        } else {
            echo "❌ Failed to generate application key.\n";
        }
    }

    /**
     * List all routes
     */
    private function routeList(array $args): void
    {
        echo "Fusion Framework Routes\n";
        echo str_repeat("=", 50) . "\n\n";

        try {
            // Load all route files
            $routeFiles = glob('app/modules/*/routes.php');
            $routes = [];

            foreach ($routeFiles as $file) {
                $module = basename(dirname($file));
                $router = new Router();

                // Include route file to register routes
                ob_start();
                include $file;
                ob_end_clean();

                // Get registered routes (this would need to be implemented in Router class)
                $moduleRoutes = $this->getModuleRoutes($module);
                $routes = array_merge($routes, $moduleRoutes);
            }

            if (empty($routes)) {
                echo "No routes found. Create some routes in your modules.\n";
                return;
            }

            foreach ($routes as $route) {
                $method = str_pad($route['method'], 8);
                $uri = str_pad($route['uri'], 30);
                $action = $route['action'];
                echo "{$method} {$uri} {$action}\n";
            }
        } catch (\Exception $e) {
            echo "Error loading routes: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Get module routes (placeholder - would need Router implementation)
     */
    private function getModuleRoutes(string $module): array
    {
        // This is a placeholder - in a real implementation, the Router class
        // would need to expose its registered routes
        return [
            [
                'method' => 'GET',
                'uri' => '/',
                'action' => "{$module}Controller@index"
            ]
        ];
    }

    /**
     * Clear application cache
     */
    private function cacheClear(array $args): void
    {
        echo "Clearing application cache...\n";

        try {
            $cacheDir = 'storage/cache';
            if (is_dir($cacheDir)) {
                $this->clearDirectory($cacheDir);
                echo "✅ Cache cleared successfully!\n";
            } else {
                echo "⚠️  Cache directory not found.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Failed to clear cache: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Cache configuration
     */
    private function configCache(array $args): void
    {
        echo "Caching configuration...\n";

        try {
            $configDir = 'config';
            $cacheFile = 'storage/cache/config.php';

            if (!is_dir('storage/cache')) {
                mkdir('storage/cache', 0755, true);
            }

            $config = [];
            $configFiles = glob($configDir . '/*.php');

            foreach ($configFiles as $file) {
                $key = basename($file, '.php');
                $config[$key] = include $file;
            }

            $cacheContent = "<?php\nreturn " . var_export($config, true) . ";\n";

            if (file_put_contents($cacheFile, $cacheContent)) {
                echo "✅ Configuration cached successfully!\n";
            } else {
                echo "❌ Failed to cache configuration.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Failed to cache configuration: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Run database seeders
     */
    private function dbSeed(array $args): void
    {
        echo "Running database seeders...\n";

        try {
            $seederDir = 'database/seeders';
            if (!is_dir($seederDir)) {
                echo "⚠️  No seeders directory found.\n";
                return;
            }

            $seeders = glob($seederDir . '/*Seeder.php');
            if (empty($seeders)) {
                echo "⚠️  No seeders found.\n";
                return;
            }

            foreach ($seeders as $seeder) {
                $className = basename($seeder, '.php');
                echo "Running seeder: {$className}\n";

                require_once $seeder;
                $seederInstance = new $className();
                $seederInstance->run();
            }

            echo "✅ Seeders completed successfully!\n";
        } catch (\Exception $e) {
            echo "❌ Seeding failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Optimize application
     */
    private function optimize(array $args): void
    {
        echo "Optimizing application...\n";

        try {
            // Clear cache
            $this->cacheClear([]);

            // Cache config
            $this->configCache([]);

            // Optimize autoloader
            echo "Optimizing autoloader...\n";
            if (file_exists('composer.json')) {
                passthru('composer dump-autoload --optimize');
            }

            echo "✅ Application optimized successfully!\n";
        } catch (\Exception $e) {
            echo "❌ Optimization failed: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Make seeder command
     */
    private function makeSeeder(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:seeder <SeederName>\n";
            return;
        }

        $name = $args[0];
        $this->generateSeeder($name);
    }

    /**
     * Make factory command
     */
    private function makeFactory(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:factory <FactoryName>\n";
            return;
        }

        $name = $args[0];
        $this->generateFactory($name);
    }

    /**
     * Make request command
     */
    private function makeRequest(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:request <RequestName>\n";
            return;
        }

        $name = $args[0];
        $this->generateRequest($name);
    }

    /**
     * Make job command
     */
    private function makeJob(array $args): void
    {
        if (empty($args)) {
            echo "Usage: make:job <JobName>\n";
            return;
        }

        $name = $args[0];
        $this->generateJob($name);
    }


    /**
     * Queue restart command
     */
    private function queueRestart(array $args): void
    {
        echo "Restarting queue workers...\n";

        // This would need a proper queue implementation
        echo "⚠️  Queue restart not implemented yet.\n";
    }

    /**
     * Storage link command
     */
    private function storageLink(array $args): void
    {
        echo "Creating storage link...\n";

        try {
            $target = 'storage/app/public';
            $link = 'public/storage';

            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }

            if (is_link($link)) {
                unlink($link);
            }

            if (symlink($target, $link)) {
                echo "✅ Storage link created successfully!\n";
            } else {
                echo "❌ Failed to create storage link.\n";
            }
        } catch (\Exception $e) {
            echo "❌ Failed to create storage link: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Tinker command
     */
    private function tinker(array $args): void
    {
        echo "Fusion Framework Tinker\n";
        echo "Type 'exit' to quit.\n\n";

        // Simple interactive shell
        while (true) {
            $input = readline('fusion> ');

            if (trim($input) === 'exit') {
                break;
            }

            if (empty($input)) {
                continue;
            }

            try {
                eval($input . ';');
            } catch (\Exception $e) {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * Generate seeder file
     */
    private function generateSeeder(string $name): void
    {
        $seederDir = 'database/seeders';
        if (!is_dir($seederDir)) {
            mkdir($seederDir, 0755, true);
        }

        $content = "<?php\n\nclass {$name}Seeder\n{\n    public function run()\n    {\n        // Add your seeder logic here\n    }\n}\n";

        file_put_contents("{$seederDir}/{$name}Seeder.php", $content);
        echo "Seeder created: {$seederDir}/{$name}Seeder.php\n";
    }

    /**
     * Generate factory file
     */
    private function generateFactory(string $name): void
    {
        $factoryDir = 'database/factories';
        if (!is_dir($factoryDir)) {
            mkdir($factoryDir, 0755, true);
        }

        $content = "<?php\n\nclass {$name}Factory\n{\n    public function definition()\n    {\n        return [\n            // Add your factory definition here\n        ];\n    }\n}\n";

        file_put_contents("{$factoryDir}/{$name}Factory.php", $content);
        echo "Factory created: {$factoryDir}/{$name}Factory.php\n";
    }

    /**
     * Generate request file
     */
    private function generateRequest(string $name): void
    {
        $requestDir = 'app/Requests';
        if (!is_dir($requestDir)) {
            mkdir($requestDir, 0755, true);
        }

        $content = "<?php\n\nnamespace App\\Requests;\n\nuse Fusion\\Core\\Request;\n\nclass {$name}Request extends Request\n{\n    public function rules()\n    {\n        return [\n            // Add your validation rules here\n        ];\n    }\n\n    public function messages()\n    {\n        return [\n            // Add your custom error messages here\n        ];\n    }\n}\n";

        file_put_contents("{$requestDir}/{$name}Request.php", $content);
        echo "Request created: {$requestDir}/{$name}Request.php\n";
    }

    /**
     * Generate job file
     */
    private function generateJob(string $name): void
    {
        $jobDir = 'app/Jobs';
        if (!is_dir($jobDir)) {
            mkdir($jobDir, 0755, true);
        }

        $content = "<?php\n\nnamespace App\\Jobs;\n\nclass {$name}Job\n{\n    public function handle()\n    {\n        // Add your job logic here\n    }\n}\n";

        file_put_contents("{$jobDir}/{$name}Job.php", $content);
        echo "Job created: {$jobDir}/{$name}Job.php\n";
    }

    /**
     * Clear directory recursively
     */
    private function clearDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;

        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            } elseif (is_dir($file)) {
                $this->clearDirectory($file);
                rmdir($file);
            }
        }
    }
}
