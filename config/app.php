<?php

declare(strict_types=1);

return [

    'name' => 'Survey',

    'version' => env('APP_VERSION', 'dev'),

    'url' => env('APP_URL', 'http://localhost'),

    'locales' => [
        'en' => 'English',
        'de' => 'Deutsch',
    ],

    'allow_registration' => env('APP_ALLOW_REGISTRATION', true),

    'enable_email_verification' => env('APP_ENABLE_EMAIL_VERIFICATION', false),

    'enable_password_reset' => env('APP_ENABLE_PASSWORD_RESET', false),

];
