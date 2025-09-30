<?php

return [
    'default' => $_ENV['QUEUE_DRIVER'] ?? 'sync',

    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],

        'file' => [
            'driver' => 'file',
            'path' => $_ENV['QUEUE_PATH'] ?? 'storage/queue',
        ],

        'redis' => [
            'driver' => 'redis',
            'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['REDIS_PORT'] ?? '6379',
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
            'database' => $_ENV['REDIS_DATABASE'] ?? 0,
            'queue' => $_ENV['QUEUE_REDIS_QUEUE'] ?? 'fusion_jobs',
        ],

        'beanstalk' => [
            'driver' => 'beanstalk',
            'host' => $_ENV['BEANSTALK_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['BEANSTALK_PORT'] ?? '11300',
            'queue' => $_ENV['BEANSTALK_QUEUE'] ?? 'fusion_jobs',
        ],

        'rabbitmq' => [
            'driver' => 'rabbitmq',
            'host' => $_ENV['RABBITMQ_HOST'] ?? '127.0.0.1',
            'port' => $_ENV['RABBITMQ_PORT'] ?? '5672',
            'user' => $_ENV['RABBITMQ_USER'] ?? 'guest',
            'password' => $_ENV['RABBITMQ_PASS'] ?? 'guest',
            'queue' => $_ENV['RABBITMQ_QUEUE'] ?? 'fusion_jobs',
        ],

        'sqs' => [
            'driver' => 'sqs',
            'key' => $_ENV['AWS_KEY'] ?? '',
            'secret' => $_ENV['AWS_SECRET'] ?? '',
            'region' => $_ENV['AWS_REGION'] ?? 'us-east-1',
            'queue_url' => $_ENV['SQS_QUEUE_URL'] ?? '',
            'failed_queue_url' => $_ENV['SQS_FAILED_QUEUE_URL'] ?? '',
        ],
    ],

    'failed' => [
        'driver' => 'file',
        'path' => $_ENV['QUEUE_FAILED_PATH'] ?? 'storage/queue/failed',
    ],

    'retry_after' => $_ENV['QUEUE_RETRY_AFTER'] ?? 90,
    'max_tries' => $_ENV['QUEUE_MAX_TRIES'] ?? 3,
    'timeout' => $_ENV['QUEUE_TIMEOUT'] ?? 60,
];
