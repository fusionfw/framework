<?php

namespace Fusion\Queue;

use Fusion\Queue\Drivers\SyncQueueDriver;
use Fusion\Queue\Drivers\FileQueueDriver;
use Fusion\Config;

/**
 * Queue Manager
 */
class QueueManager
{
    /**
     * Available drivers
     *
     * @var array
     */
    private $drivers = [];

    /**
     * Default driver
     *
     * @var string
     */
    private $defaultDriver;

    /**
     * Config instance
     *
     * @var Config
     */
    private $config;

    /**
     * Create a new queue manager
     *
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->defaultDriver = $config->get('queue.default', 'sync');
        $this->registerDrivers();
    }

    /**
     * Register available drivers
     *
     * @return void
     */
    private function registerDrivers(): void
    {
        $this->drivers = [
            'sync' => SyncQueueDriver::class,
            'file' => FileQueueDriver::class,
            'redis' => \Fusion\Core\Queue\Drivers\RedisQueueDriver::class,
            'beanstalk' => \Fusion\Core\Queue\Drivers\BeanstalkQueueDriver::class,
            'rabbitmq' => \Fusion\Core\Queue\Drivers\RabbitMQQueueDriver::class,
            'sqs' => \Fusion\Core\Queue\Drivers\SqsQueueDriver::class,
        ];
    }

    /**
     * Get a queue driver instance
     *
     * @param string|null $driver
     * @return QueueDriverInterface
     */
    public function driver(string $driver = null): QueueDriverInterface
    {
        $driver = $driver ?: $this->defaultDriver;

        if (!isset($this->drivers[$driver])) {
            throw new \InvalidArgumentException("Queue driver [{$driver}] is not supported.");
        }

        return $this->createDriver($driver);
    }

    /**
     * Create a driver instance
     *
     * @param string $driver
     * @return QueueDriverInterface
     */
    private function createDriver(string $driver): QueueDriverInterface
    {
        $driverClass = $this->drivers[$driver];

        switch ($driver) {
            case 'sync':
                return new $driverClass();

            case 'file':
                $queuePath = $this->config->get('queue.path', 'storage/queue');
                return new $driverClass($queuePath);

            case 'redis':
                $config = $this->config->get('queue.connections.redis', []);
                return new $driverClass($config);

            case 'beanstalk':
                $config = $this->config->get('queue.connections.beanstalk', []);
                return new $driverClass($config);

            case 'rabbitmq':
                $config = $this->config->get('queue.connections.rabbitmq', []);
                return new $driverClass($config);

            case 'sqs':
                $config = $this->config->get('queue.connections.sqs', []);
                return new $driverClass($config);

            default:
                throw new \InvalidArgumentException("Driver [{$driver}] not supported.");
        }
    }

    /**
     * Push a job to the queue
     *
     * @param string $job Job class name
     * @param array $data Job data
     * @param int $delay Delay in seconds
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function push(string $job, array $data = [], int $delay = 0, string $driver = null): bool
    {
        return $this->driver($driver)->push($job, $data, $delay);
    }

    /**
     * Pop a job from the queue
     *
     * @param string|null $driver Queue driver
     * @return array|null
     */
    public function pop(string $driver = null): ?array
    {
        return $this->driver($driver)->pop();
    }

    /**
     * Get the number of jobs in the queue
     *
     * @param string|null $driver Queue driver
     * @return int
     */
    public function size(string $driver = null): int
    {
        return $this->driver($driver)->size();
    }

    /**
     * Clear all jobs from the queue
     *
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function clear(string $driver = null): bool
    {
        return $this->driver($driver)->clear();
    }

    /**
     * Acknowledge a job as completed
     *
     * @param string $jobId Job ID
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function ack(string $jobId, string $driver = null): bool
    {
        return $this->driver($driver)->ack($jobId);
    }

    /**
     * Mark a job as failed
     *
     * @param string $jobId Job ID
     * @param string $reason Failure reason
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function fail(string $jobId, string $reason, string $driver = null): bool
    {
        return $this->driver($driver)->fail($jobId, $reason);
    }

    /**
     * Mark a job as failed (legacy method)
     *
     * @param array $job Job data
     * @param string $error Error message
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function markAsFailed(array $job, string $error, string $driver = null): bool
    {
        return $this->driver($driver)->markAsFailed($job, $error);
    }

    /**
     * Get failed jobs
     *
     * @param string|null $driver Queue driver
     * @return array
     */
    public function getFailed(string $driver = null): array
    {
        return $this->driver($driver)->getFailed();
    }

    /**
     * Retry a failed job
     *
     * @param string $jobId Failed job ID
     * @param string|null $driver Queue driver
     * @return bool
     */
    public function retry(string $jobId, string $driver = null): bool
    {
        return $this->driver($driver)->retry($jobId);
    }

    /**
     * Get available drivers
     *
     * @return array
     */
    public function getAvailableDrivers(): array
    {
        return array_keys($this->drivers);
    }

    /**
     * Check if driver is supported
     *
     * @param string $driver
     * @return bool
     */
    public function isDriverSupported(string $driver): bool
    {
        return isset($this->drivers[$driver]);
    }
}
