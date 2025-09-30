<?php

namespace Fusion\Core\Queue\Drivers;

use Fusion\Core\Queue\QueueDriverInterface;
use Aws\Sqs\SqsClient;
use Aws\Exception\AwsException;

/**
 * Amazon SQS Queue Driver
 */
class SqsQueueDriver implements QueueDriverInterface
{
    /**
     * SQS client
     *
     * @var SqsClient
     */
    private $sqs;

    /**
     * Queue URL
     *
     * @var string
     */
    private $queueUrl;

    /**
     * Failed queue URL
     *
     * @var string
     */
    private $failedQueueUrl;

    /**
     * Create a new SQS queue driver
     *
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->sqs = new SqsClient([
            'version' => 'latest',
            'region' => $config['region'] ?? 'us-east-1',
            'credentials' => [
                'key' => $config['key'] ?? '',
                'secret' => $config['secret'] ?? ''
            ]
        ]);

        $this->queueUrl = $config['queue_url'] ?? '';
        $this->failedQueueUrl = $config['failed_queue_url'] ?? $this->queueUrl . '_failed';
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

            $params = [
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => json_encode($jobData),
                'MessageAttributes' => [
                    'JobClass' => [
                        'DataType' => 'String',
                        'StringValue' => $job
                    ],
                    'JobId' => [
                        'DataType' => 'String',
                        'StringValue' => $jobData['id']
                    ]
                ]
            ];

            if ($delay > 0) {
                $params['DelaySeconds'] = min($delay, 900); // SQS max delay is 15 minutes
            }

            $this->sqs->sendMessage($params);
            return true;
        } catch (AwsException $e) {
            echo "Failed to push job to SQS: " . $e->getMessage() . "\n";
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
            $result = $this->sqs->receiveMessage([
                'QueueUrl' => $this->queueUrl,
                'MaxNumberOfMessages' => 1,
                'WaitTimeSeconds' => 1,
                'MessageAttributeNames' => ['All']
            ]);

            $messages = $result->get('Messages');
            if (empty($messages)) {
                return null;
            }

            $message = $messages[0];
            $payload = $message['Body'];
            $jobData = json_decode($payload, true);

            if ($jobData === null) {
                // Delete invalid message
                $this->sqs->deleteMessage([
                    'QueueUrl' => $this->queueUrl,
                    'ReceiptHandle' => $message['ReceiptHandle']
                ]);
                return null;
            }

            // Store receipt handle for ack/fail operations
            $jobData['_sqs_receipt_handle'] = $message['ReceiptHandle'];

            return $jobData;
        } catch (AwsException $e) {
            echo "Failed to pop job from SQS: " . $e->getMessage() . "\n";
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
            $result = $this->sqs->getQueueAttributes([
                'QueueUrl' => $this->queueUrl,
                'AttributeNames' => ['ApproximateNumberOfMessages']
            ]);

            $attributes = $result->get('Attributes');
            return (int) ($attributes['ApproximateNumberOfMessages'] ?? 0);
        } catch (AwsException $e) {
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
            // Purge queue
            $this->sqs->purgeQueue([
                'QueueUrl' => $this->queueUrl
            ]);
            return true;
        } catch (AwsException $e) {
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
            // For SQS, ack is handled by deleting the message
            // This is a simplified implementation
            return true;
        } catch (AwsException $e) {
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

            $this->sqs->sendMessage([
                'QueueUrl' => $this->failedQueueUrl,
                'MessageBody' => json_encode($failedJob),
                'MessageAttributes' => [
                    'JobId' => [
                        'DataType' => 'String',
                        'StringValue' => $jobId
                    ]
                ]
            ]);

            return true;
        } catch (AwsException $e) {
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

            // Get messages from failed queue
            $result = $this->sqs->receiveMessage([
                'QueueUrl' => $this->failedQueueUrl,
                'MaxNumberOfMessages' => 10,
                'MessageAttributeNames' => ['All']
            ]);

            $messages = $result->get('Messages', []);

            foreach ($messages as $message) {
                $payload = $message['Body'];
                $jobData = json_decode($payload, true);

                if ($jobData !== null) {
                    $failedJobs[] = $jobData;
                }
            }

            return $failedJobs;
        } catch (AwsException $e) {
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
            $this->sqs->sendMessage([
                'QueueUrl' => $this->queueUrl,
                'MessageBody' => json_encode($job),
                'MessageAttributes' => [
                    'JobClass' => [
                        'DataType' => 'String',
                        'StringValue' => $job['job']
                    ],
                    'JobId' => [
                        'DataType' => 'String',
                        'StringValue' => $job['id']
                    ]
                ]
            ]);

            return true;
        } catch (AwsException $e) {
            echo "Failed to retry job: " . $e->getMessage() . "\n";
            return false;
        }
    }
}
