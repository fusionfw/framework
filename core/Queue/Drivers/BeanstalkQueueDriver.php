<?php

namespace Fusion\Core\Queue\Drivers;

use Fusion\Core\Queue\QueueDriverInterface;
use Pheanstalk\Pheanstalk;

/**
 * Beanstalk Queue Driver
 */
class BeanstalkQueueDriver implements QueueDriverInterface
{
    /**
     * Beanstalk client
     *
     * @var Pheanstalk
     */
    private $pheanstalk;

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
     * Create a new Beanstalk queue driver
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->pheanstalk = Pheanstalk::create(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 11300
        );

        $this->queueName = $config['queue'] ?? 'fusion_jobs';
        $this->failedQueueName = $this->queueName . '_failed';
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
            $priority = 1024; // Default priority
            $ttr = 60; // Time to run in seconds

            $this->pheanstalk
                ->useTube($this->queueName)
                ->put($payload, $priority, $delay, $ttr);

            return true;
        } catch (\Throwable $e) {
            echo "Failed to push job to Beanstalk: " . $e->getMessage() . "\n";
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
            $job = $this->pheanstalk
                ->watchOnly($this->queueName)
                ->reserve(1); // 1 second timeout

            if ($job === null) {
                return null;
            }

            $payload = $job->getData();
            $jobData = json_decode($payload, true);

            if ($jobData === null) {
                $this->pheanstalk->delete($job);
                return null;
            }

            // Store job ID for ack/fail operations
            $jobData['_beanstalk_job_id'] = $job->getId();
            $jobData['_beanstalk_job'] = $job;

            return $jobData;
        } catch (\Throwable $e) {
            echo "Failed to pop job from Beanstalk: " . $e->getMessage() . "\n";
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
            $stats = $this->pheanstalk->statsTube($this->queueName);
            return $stats['current-jobs-ready'] ?? 0;
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
            // Delete all ready jobs
            while (true) {
                $job = $this->pheanstalk
                    ->watchOnly($this->queueName)
                    ->reserve(0);

                if ($job === null) {
                    break;
                }

                $this->pheanstalk->delete($job);
            }

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
            // For Beanstalk, we need to find the job by ID
            // This is a simplified implementation
            // In production, you might want to maintain a mapping
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

            $this->pheanstalk
                ->useTube($this->failedQueueName)
                ->put(json_encode($failedJob));

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

            // Peek at failed jobs without removing them
            while (true) {
                $job = $this->pheanstalk
                    ->watchOnly($this->failedQueueName)
                    ->peekReady();

                if ($job === null) {
                    break;
                }

                $payload = $job->getData();
                $jobData = json_decode($payload, true);

                if ($jobData !== null) {
                    $failedJobs[] = $jobData;
                }

                // Move to next job
                $this->pheanstalk->delete($job);
                $this->pheanstalk
                    ->useTube($this->failedQueueName)
                    ->put($payload);
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
            $this->pheanstalk
                ->useTube($this->queueName)
                ->put(json_encode($job));

            return true;
        } catch (\Throwable $e) {
            echo "Failed to retry job: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
