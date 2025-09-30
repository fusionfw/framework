<?php

namespace Fusion\Core\Queue\Drivers;

use Fusion\Core\Queue\QueueDriverInterface;
use Fusion\Core\Queue\Job;

/**
 * Sync Queue Driver - Execute jobs immediately
 */
class SyncQueueDriver implements QueueDriverInterface
{
    /**
     * Push a job to the queue (execute immediately)
     *
     * @param string $job Job class name
     * @param array $data Job data
     * @param int $delay Delay in seconds (ignored in sync driver)
     * @return bool
     */
    public function push(string $job, array $data = [], int $delay = 0): bool
    {
        try {
            if (!class_exists($job)) {
                throw new \Exception("Job class {$job} not found");
            }

            if (!is_subclass_of($job, Job::class)) {
                throw new \Exception("Job class {$job} must extend " . Job::class);
            }

            $jobInstance = new $job($data);
            $jobInstance->handle();

            return true;
        } catch (\Throwable $e) {
            echo "Job execution failed: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Pop a job from the queue (not applicable for sync driver)
     *
     * @return array|null
     */
    public function pop(): ?array
    {
        return null;
    }

    /**
     * Get the number of jobs in the queue (always 0 for sync driver)
     *
     * @return int
     */
    public function size(): int
    {
        return 0;
    }

    /**
     * Clear all jobs from the queue (not applicable for sync driver)
     *
     * @return bool
     */
    public function clear(): bool
    {
        return true;
    }

    /**
     * Acknowledge a job as completed (not applicable for sync driver)
     *
     * @param string $jobId Job ID
     * @return bool
     */
    public function ack(string $jobId): bool
    {
        return true;
    }

    /**
     * Mark a job as failed (not applicable for sync driver)
     *
     * @param string $jobId Job ID
     * @param string $reason Failure reason
     * @return bool
     */
    public function fail(string $jobId, string $reason): bool
    {
        return true;
    }

    /**
     * Mark a job as failed (legacy method - not applicable for sync driver)
     *
     * @param array $job Job data
     * @param string $error Error message
     * @return bool
     */
    public function markAsFailed(array $job, string $error): bool
    {
        return true;
    }

    /**
     * Get failed jobs (not applicable for sync driver)
     *
     * @return array
     */
    public function getFailed(): array
    {
        return [];
    }

    /**
     * Retry a failed job (not applicable for sync driver)
     *
     * @param string $jobId Failed job ID
     * @return bool
     */
    public function retry(string $jobId): bool
    {
        return true;
    }
}
