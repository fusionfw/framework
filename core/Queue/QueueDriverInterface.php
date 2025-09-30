<?php

namespace Fusion\Core\Queue;

/**
 * Queue Driver Interface
 */
interface QueueDriverInterface
{
    /**
     * Push a job to the queue
     *
     * @param string $job Job class name
     * @param array $data Job data
     * @param int $delay Delay in seconds
     * @return bool
     */
    public function push(string $job, array $data = [], int $delay = 0): bool;

    /**
     * Pop a job from the queue
     *
     * @return array|null Job data or null if empty
     */
    public function pop(): ?array;

    /**
     * Get the number of jobs in the queue
     *
     * @return int
     */
    public function size(): int;

    /**
     * Clear all jobs from the queue
     *
     * @return bool
     */
    public function clear(): bool;

    /**
     * Acknowledge a job as completed
     *
     * @param string $jobId Job ID
     * @return bool
     */
    public function ack(string $jobId): bool;

    /**
     * Mark a job as failed
     *
     * @param string $jobId Job ID
     * @param string $reason Failure reason
     * @return bool
     */
    public function fail(string $jobId, string $reason): bool;

    /**
     * Mark a job as failed (legacy method)
     *
     * @param array $job Job data
     * @param string $error Error message
     * @return bool
     */
    public function markAsFailed(array $job, string $error): bool;

    /**
     * Get failed jobs
     *
     * @return array
     */
    public function getFailed(): array;

    /**
     * Retry a failed job
     *
     * @param string $jobId Failed job ID
     * @return bool
     */
    public function retry(string $jobId): bool;
}
