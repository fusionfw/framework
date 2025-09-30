<?php

namespace App\Jobs;

use Fusion\Core\Queue\Job;

/**
 * Send Email Job
 */
class SendEmailJob extends Job
{
    /**
     * Execute the job
     *
     * @return void
     */
    public function handle(): void
    {
        $email = $this->data['email'] ?? 'user@example.com';
        $subject = $this->data['subject'] ?? 'Welcome!';
        $message = $this->data['message'] ?? 'Hello from Fusion Framework!';

        echo "Sending email to {$email}\n";
        echo "Subject: {$subject}\n";
        echo "Message: {$message}\n";
        echo "Email sent successfully!\n\n";

        // Simulate email sending delay
        sleep(1);
    }
}
