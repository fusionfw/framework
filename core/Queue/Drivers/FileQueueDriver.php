<?php

namespace Fusion\Core\Queue\Drivers;

use Fusion\Core\Queue\QueueDriverInterface;

/**
 * File Queue Driver - Store jobs in JSON files
 */
class FileQueueDriver implements QueueDriverInterface
{
    /**
     * Queue file path
     *
     * @var string
     */
    private $queuePath;

    /**
     * Failed jobs file path
     *
     * @var string
     */
    private $failedPath;

    /**
     * Create a new file queue driver
     *
     * @param string $queuePath
     */
    public function __construct(string $queuePath)
    {
        $this->queuePath = rtrim($queuePath, '/') . '/queue.json';
        $this->failedPath = rtrim($queuePath, '/') . '/failed.json';

        $this->ensureDirectoryExists(dirname($this->queuePath));
        $this->ensureDirectoryExists(dirname($this->failedPath));
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

            $jobs = $this->loadJobs();
            $jobs[] = $jobData;

            return $this->saveJobs($jobs);
        } catch (\Throwable $e) {
            echo "Failed to push job: " . $e->getMessage() . "\n";
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
        $jobs = $this->loadJobs();
        $now = time();

        foreach ($jobs as $index => $job) {
            if ($job['available_at'] <= $now) {
                // Remove job from queue
                unset($jobs[$index]);
                $this->saveJobs(array_values($jobs));

                return $job;
            }
        }

        return null;
    }

    /**
     * Get the number of jobs in the queue
     *
     * @return int
     */
    public function size(): int
    {
        $jobs = $this->loadJobs();
        $now = time();
        $availableJobs = 0;

        foreach ($jobs as $job) {
            if ($job['available_at'] <= $now) {
                $availableJobs++;
            }
        }

        return $availableJobs;
    }

    /**
     * Clear all jobs from the queue
     *
     * @return bool
     */
    public function clear(): bool
    {
        return $this->saveJobs([]);
    }

    /**
     * Acknowledge a job as completed
     *
     * @param string $jobId Job ID
     * @return bool
     */
    public function ack(string $jobId): bool
    {
        // For file driver, ack is handled by removing from queue in pop()
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

            $failedJobs = $this->loadFailedJobs();
            $failedJobs[] = $failedJob;

            return $this->saveFailedJobs($failedJobs);
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
        return $this->loadFailedJobs();
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
            $failedJobs = $this->loadFailedJobs();
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
            unset($failedJobs[$jobIndex]);

            // Reset job data
            unset($job['failed_at']);
            unset($job['error']);
            $job['available_at'] = time();
            $job['attempts'] = 0;

            // Add back to queue
            $jobs = $this->loadJobs();
            $jobs[] = $job;

            $this->saveJobs($jobs);
            $this->saveFailedJobs(array_values($failedJobs));

            return true;
        } catch (\Throwable $e) {
            echo "Failed to retry job: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Load jobs from file
     *
     * @return array
     */
    private function loadJobs(): array
    {
        if (!file_exists($this->queuePath)) {
            return [];
        }

        $content = file_get_contents($this->queuePath);
        return json_decode($content, true) ?: [];
    }

    /**
     * Save jobs to file
     *
     * @param array $jobs
     * @return bool
     */
    private function saveJobs(array $jobs): bool
    {
        return file_put_contents($this->queuePath, json_encode($jobs, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Load failed jobs from file
     *
     * @return array
     */
    private function loadFailedJobs(): array
    {
        if (!file_exists($this->failedPath)) {
            return [];
        }

        $content = file_get_contents($this->failedPath);
        return json_decode($content, true) ?: [];
    }

    /**
     * Save failed jobs to file
     *
     * @param array $jobs
     * @return bool
     */
    private function saveFailedJobs(array $jobs): bool
    {
        return file_put_contents($this->failedPath, json_encode($jobs, JSON_PRETTY_PRINT)) !== false;
    }

    /**
     * Ensure directory exists
     *
     * @param string $path
     * @return void
     */
    private function ensureDirectoryExists(string $path): void
    {
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
    }
}
