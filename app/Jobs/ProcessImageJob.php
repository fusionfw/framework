<?php

namespace App\Jobs;

use Fusion\Core\Queue\Job;

/**
 * Process Image Job
 */
class ProcessImageJob extends Job
{
    /**
     * Execute the job
     *
     * @return void
     */
    public function handle(): void
    {
        $imagePath = $this->data['image_path'] ?? 'unknown';
        $operation = $this->data['operation'] ?? 'resize';
        $width = $this->data['width'] ?? 800;
        $height = $this->data['height'] ?? 600;

        echo "Processing image: {$imagePath}\n";
        echo "Operation: {$operation}\n";
        echo "Dimensions: {$width}x{$height}\n";
        echo "Image processed successfully!\n\n";

        // Simulate image processing delay
        sleep(2);
    }
}
