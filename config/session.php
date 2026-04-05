<?php

declare(strict_types=1);

return [

    'driver' => env('SESSION_DRIVER', 'database'),

    'table' => env('SESSION_TABLE', 'sys_sessions'),

    'cookie' => 'survey_session',

];
