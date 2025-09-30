<?php

namespace Fusion\Queue\Drivers;

use Fusion\\Queue\QueueDriverInterface;
use Predis\Client;

/**
 * Redis Queue Driver
 */
class RedisQueueDriver implements QueueDriverInterface
{
    /**
     * Redis client
     *
     * @var Client
     */
    private $redis;

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
     * Create a new Redis queue driver
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->redis = new Client([
            'host' => $config['host'] ?? '127.0.0.1',
            'port' => $config['port'] ?? 6379,
            'database' => $config['database'] ?? 0,
            'password' => $config['password'] ?? null,
        ]);

        $this->queueName = $config['queue'] ?? 'fusion_jobs';
        $this->failedQueueName = $this->queueName . ':failed';
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

            if ($delay > 0) {
                // Use delayed queue
                $this->redis->zadd($this->queueName . ':delayed', $jobData['available_at'], $payload);
            } else {
                // Push to immediate queue
                $this->redis->lpush($this->queueName, $payload);
            }

            return true;
        } catch (\Throwable $e) {
            echo "Failed to push job to Redis: " . $e->getMessage() . "\n";
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
            // First, move delayed jobs to ready queue
            $this->moveDelayedJobs();

            // Pop from ready queue
            $result = $this->redis->brpop($this->queueName, 1);

            if ($result === null) {
                return null;
            }

            $payload = $result[1];
            $jobData = json_decode($payload, true);

            if ($jobData === null) {
                return null;
            }

            return $jobData;
        } catch (\Throwable $e) {
            echo "Failed to pop job from Redis: " . $e->getMessage() . "\n";
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
            $readySize = $this->redis->llen($this->queueName);
            $delayedSize = $this->redis->zcard($this->queueName . ':delayed');
            return $readySize + $delayedSize;
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
            $this->redis->del($this->queueName);
            $this->redis->del($this->queueName . ':delayed');
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
        // For Redis, ack is handled by removing from queue in pop()
        return true;
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

            $this->redis->lpush($this->failedQueueName, json_encode($failedJob));
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
            $failedJobs = $this->redis->lrange($this->failedQueueName, 0, -1);
            $result = [];

            foreach ($failedJobs as $job) {
                $jobData = json_decode($job, true);
                if ($jobData !== null) {
                    $result[] = $jobData;
                }
            }

            return $result;
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
            $failedJobs = $this->redis->lrange($this->failedQueueName, 0, -1);
            $jobIndex = null;

            foreach ($failedJobs as $index => $job) {
                $jobData = json_decode($job, true);
                if ($jobData && $jobData['id'] === $jobId) {
                    $jobIndex = $index;
                    break;
                }
            }

            if ($jobIndex === null) {
                return false;
            }

            // Remove from failed queue
            $this->redis->lrem($this->failedQueueName, 1, $failedJobs[$jobIndex]);

            // Add back to ready queue
            $jobData = json_decode($failedJobs[$jobIndex], true);
            unset($jobData['failed_at']);
            unset($jobData['error']);
            $jobData['available_at'] = time();
            $jobData['attempts'] = 0;

            $this->redis->lpush($this->queueName, json_encode($jobData));

            return true;
        } catch (\Throwable $e) {
            echo "Failed to retry job: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Move delayed jobs to ready queue
     *
     * @return void
     */
    private function moveDelayedJobs(): void
    {
        $now = time();
        $delayedJobs = $this->redis->zrangebyscore($this->queueName . ':delayed', 0, $now);

        if (!empty($delayedJobs)) {
            foreach ($delayedJobs as $job) {
                $this->redis->lpush($this->queueName, $job);
                $this->redis->zrem($this->queueName . ':delayed', $job);
            }
        }
    }
}
