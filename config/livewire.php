<?php

declare(strict_types=1);

return [

    'temporary_file_upload' => [
        // Livewire would normally delete all tmp files older than 24 hours
        // after every new upload. This is now done in the scheduler.
        'cleanup' => false,
    ],

];
