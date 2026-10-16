<?php

declare(strict_types=1);

use App\Console\Commands\App\AppCleanupTempDataCommand;
use Illuminate\Queue\Console\FlushFailedCommand;
use Illuminate\Queue\Console\PruneBatchesCommand;
use Illuminate\Queue\Console\PruneFailedJobsCommand;

Schedule::command(AppCleanupTempDataCommand::class)->everyThreeHours()->withoutOverlapping();

Schedule::command(PruneBatchesCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(PruneFailedJobsCommand::class)->everySixHours()->withoutOverlapping();
Schedule::command(FlushFailedCommand::class)->everySixHours()->withoutOverlapping();
