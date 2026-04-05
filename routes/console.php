<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Queue\Console\PruneBatchesCommand;
use Illuminate\Queue\Console\PruneFailedJobsCommand;
use Illuminate\Support\Facades\DB;

Schedule::job(UploadsCleanupJob::class)->everySixHours();

Schedule::command(PruneBatchesCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(PruneFailedJobsCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(FlushFailedCommand::class)->everySixHours()->withoutOverlapping();

Schedule::call(function (): void {
    DB::table(config()->string('cache.stores.database.table'))
        ->where('expiration', '<', time())
        ->delete();

    DB::table(config()->string('cache.stores.database.lock_table'))
        ->where('expiration', '<', time())
        ->delete();
})->everySixHours();
