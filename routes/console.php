<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;

Schedule::job(UploadsCleanupJob::class)->everySixHours();
