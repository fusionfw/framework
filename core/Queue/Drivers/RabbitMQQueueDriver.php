<?php

namespace Fusion\Core\Queue\Drivers;

use Fusion\Core\Queue\QueueDriverInterface;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;

/**
 * RabbitMQ Queue Driver
 */
class RabbitMQQueueDriver implements QueueDriverInterface
{
    /**
     * RabbitMQ connection
     *
     * @var AMQPStreamConnection
     */
    private $connection;

    /**
     * RabbitMQ channel
     *
     * @var AMQPChannel
     */
    private $channel;

    /**
     * Queue name
     *
     * @var string
     */
    private $queueName;

    /**
     * Failed queue name
     *
     * @var string
     */
    private $failedQueueName;

    /**
     * Create a new RabbitMQ queue driver
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->connection = new AMQPStreamConnection(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 5672,
            $config['user'] ?? 'guest',
            $config['password'] ?? 'guest'
        );

        $this->channel = $this->connection->channel();
        $this->queueName = $config['queue'] ?? 'fusion_jobs';
        $this->failedQueueName = $this->queueName . '_failed';

        // Declare queues
        $this->channel->queue_declare($this->queueName, false, true, false, false);
        $this->channel->queue_declare($this->failedQueueName, false, true, false, false);
    }

    /**
     * Push a job to the queue
     *
     * @param string $job Job class name
     * @param array $data Job data
     * @param int $delay Delay in seconds
     * @return bool
     */
    public function push(string $job, array $data = [], int $delay = 0): bool
    {
        try {
            $jobData = [
                'id' => uniqid('job_', true),
                'job' => $job,
                'data' => $data,
                'delay' => $delay,
                'created_at' => time(),
                'available_at' => time() + $delay,
                'attempts' => 0,
                'max_attempts' => 3
            ];

            $payload = json_encode($jobData);
            $message = new AMQPMessage($payload, [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $this->channel->basic_publish($message, '', $this->queueName);
            return true;
        } catch (\Throwable $e) {
            echo "Failed to push job to RabbitMQ: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Pop a job from the queue
     *
     * @return array|null Job data or null if empty
     */
    public function pop(): ?array
    {
        try {
            $message = $this->channel->basic_get($this->queueName, true);

            if ($message === null) {
                return null;
            }

            $payload = $message->getBody();
            $jobData = json_decode($payload, true);

            if ($jobData === null) {
                return null;
            }

            // Store message for ack/fail operations
            $jobData['_rabbitmq_message'] = $message;

            return $jobData;
        } catch (\Throwable $e) {
            echo "Failed to pop job from RabbitMQ: " . $e->getMessage() . "\n";
            return null;
        }
    }

    /**
     * Get the number of jobs in the queue
     *
     * @return int
     */
    public function size(): int
    {
        try {
            $result = $this->channel->queue_declare($this->queueName, true);
            return $result[1] ?? 0;
        } catch (\Throwable $e) {
            echo "Failed to get queue size: " . $e->getMessage() . "\n";
            return 0;
        }
    }

    /**
     * Clear all jobs from the queue
     *
     * @return bool
     */
    public function clear(): bool
    {
        try {
            $this->channel->queue_purge($this->queueName);
            return true;
        } catch (\Throwable $e) {
            echo "Failed to clear queue: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Acknowledge a job as completed
     *
     * @param string $jobId Job ID
     * @return bool
     */
    public function ack(string $jobId): bool
    {
        try {
            // For RabbitMQ, ack is handled by the message acknowledgment
            // This is a simplified implementation
            return true;
        } catch (\Throwable $e) {
            echo "Failed to ack job: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Mark a job as failed
     *
     * @param string $jobId Job ID
     * @param string $reason Failure reason
     * @return bool
     */
    public function fail(string $jobId, string $reason): bool
    {
        try {
            $failedJob = [
                'id' => $jobId,
                'failed_at' => time(),
                'error' => $reason
            ];

            $message = new AMQPMessage(json_encode($failedJob), [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);

            $this->channel->basic_publish($message, '', $this->failedQueueName);
            return true;
        } catch (\Throwable $e) {
            echo "Failed to mark job as failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Mark a job as failed (legacy method)
     *
     * @param array $job Job data
     * @param string $error Error message
     * @return bool
     */
    public function markAsFailed(array $job, string $error): bool
    {
        return $this->fail($job['id'] ?? '', $error);
    }

    /**
     * Get failed jobs
     *
     * @return array
     */
    public function getFailed(): array
    {
        try {
            $failedJobs = [];

            // Get all messages from failed queue
            while (true) {
                $message = $this->channel->basic_get($this->failedQueueName, true);

                if ($message === null) {
                    break;
                }

                $payload = $message->getBody();
                $jobData = json_decode($payload, true);

                if ($jobData !== null) {
                    $failedJobs[] = $jobData;
                }
            }

            // Re-queue the failed jobs for viewing
            foreach ($failedJobs as $job) {
                $message = new AMQPMessage(json_encode($job), [
                    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
                ]);
                $this->channel->basic_publish($message, '', $this->failedQueueName);
            }

            return $failedJobs;
        } catch (\Throwable $e) {
            echo "Failed to get failed jobs: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * Retry a failed job
     *
     * @param string $jobId Failed job ID
     * @return bool
     */
    public function retry(string $jobId): bool
    {
        try {
            $failedJobs = $this->getFailed();
            $jobIndex = null;

            foreach ($failedJobs as $index => $job) {
                if ($job['id'] === $jobId) {
                    $jobIndex = $index;
                    break;
                }
            }

            if ($jobIndex === null) {
                return false;
            }

            $job = $failedJobs[$jobIndex];
            unset($job['failed_at']);
            unset($job['error']);
            $job['available_at'] = time();
            $job['attempts'] = 0;

            // Add back to main queue
            $message = new AMQPMessage(json_encode($job), [
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT
            ]);
            $this->channel->basic_publish($message, '', $this->queueName);

            return true;
        } catch (\Throwable $e) {
            echo "Failed to retry job: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Close connection
     */
    public function __destruct()
    {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}
