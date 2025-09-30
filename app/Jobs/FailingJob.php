<?php

namespace App\Jobs;

use Fusion\Core\Queue\Job;

/**
 * Failing Job for testing
 */
class FailingJob extends Job
{
    /**
     * Execute the job
     *
     * @return void
     */
    public function handle(): void
    {
        throw new \Exception("This job is designed to fail for testing purposes");
    }
}
