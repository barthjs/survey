<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;
use Illuminate\Support\Facades\DB;

Schedule::job(UploadsCleanupJob::class)->everySixHours();

Schedule::command('queue:prune-batches')->everySixHours()->withoutOverlapping();
Schedule::command('queue:prune-failed')->everySixHours()->withoutOverlapping();
Schedule::command('queue:flush')->everySixHours()->withoutOverlapping();

Schedule::call(function (): void {
    DB::table(config()->string('cache.stores.database.table'))
        ->where('expiration', '<=', time())
        ->delete();

    DB::table(config()->string('cache.stores.database.lock_table'))
        ->where('expiration', '<=', time())
        ->delete();
})->daily();
