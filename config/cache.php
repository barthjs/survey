<?php

declare(strict_types=1);

return [

    'stores' => [

        'database' => [
            'driver' => 'database',
            'connection' => env('DB_CACHE_CONNECTION'),
            'table' => 'sys_cache',
            'lock_connection' => env('DB_CACHE_LOCK_CONNECTION'),
            'lock_table' => 'sys_cache_locks',
        ],

    ],

    'prefix' => 'survey_cache_',

];
