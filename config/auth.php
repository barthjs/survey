<?php

declare(strict_types=1);

return [

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => 'sys_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

];
