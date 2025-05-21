<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;

Schedule::job(UploadsCleanupJob::class)->everySixHours();

Schedule::call(function () {
    DB::table('sys_cache')
        ->where('expiration', '<=', time())
        ->delete();
})->daily();
