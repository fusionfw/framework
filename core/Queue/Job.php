<?php

namespace Fusion\Core\Queue;

/**
 * Base Job Class
 */
abstract class Job
{
    /**
     * Job data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Job attempts
     *
     * @var int
     */
    protected $attempts = 1;

    /**
     * Job timeout in seconds
     *
     * @var int
     */
    protected $timeout = 60;

    /**
     * Create a new job instance
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Execute the job
     *
     * @return void
     */
    abstract public function handle(): void;

    /**
     * Get job data
     *
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get job attempts
     *
     * @return int
     */
    public function getAttempts(): int
    {
        return $this->attempts;
    }

    /**
     * Get job timeout
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Set job attempts
     *
     * @param int $attempts
     * @return void
     */
    public function setAttempts(int $attempts): void
    {
        $this->attempts = $attempts;
    }

    /**
     * Set job timeout
     *
     * @param int $timeout
     * @return void
     */
    public function setTimeout(int $timeout): void
    {
        $this->timeout = $timeout;
    }

    /**
     * Handle a job failure
     *
     * @param \Throwable $exception
     * @return void
     */
    public function failed(\Throwable $exception): void
    {
        // Override in child class if needed
    }
}
