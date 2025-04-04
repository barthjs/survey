<?php

declare(strict_types=1);

return [

    'default' => 'database',

    'connections' => [

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_QUEUE_CONNECTION'),
            'table' => 'sys_jobs',
            'queue' => env('DB_QUEUE', 'default'),
            'retry_after' => (int) env('DB_QUEUE_RETRY_AFTER', 90),
            'after_commit' => false,
        ],

    ],

    'batching' => [
        'database' => env('DB_CONNECTION', 'mariadb'),
        'table' => 'sys_job_batches',
    ],

    'failed' => [
        'driver' => 'database-uuids',
        'database' => env('DB_CONNECTION', 'mariadb'),
        'table' => 'sys_failed_jobs',
    ],

];
