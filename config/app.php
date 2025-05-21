<?php

declare(strict_types=1);

return [

    'name' => env('APP_NAME', 'Survey'),

    'version' => env('APP_VERSION', 'dev'),

    'locale' => env('APP_LOCALE', 'en'),

    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'en'),

    'faker_locale' => env('APP_FAKER_LOCALE', 'en_US'),

    'locales' => [
        'en' => 'English',
        'de' => 'Deutsch',
    ],

    'allow_registration' => env('APP_ALLOW_REGISTRATION', true),

    'enable_email_verification' => env('APP_ENABLE_EMAIL_VERIFICATION', false),

    'enable_password_reset' => env('APP_ENABLE_PASSWORD_RESET', false),

];
