<?php

declare(strict_types=1);

namespace App\Console\Commands\App;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Clean up old temporary data.
 *
 * - Cache: Delete expired cache entries and locks.
 * - Livewire temporary files: Delete all files older than 24 hours.
 */
#[Signature('app:cleanup-temp-data')]
#[Description('Cleanup old temporary data')]
final class AppCleanupTempDataCommand extends Command
{
    public function handle(): int
    {
        $this->cleanupCache();
        $this->cleanupLivewireTmp();

        return self::SUCCESS;
    }

    private function cleanupCache(): void
    {
        DB::table(config()->string('cache.stores.database.table'))
            ->where('expiration', '<', time())
            ->delete();

        DB::table(config()->string('cache.stores.database.lock_table'))
            ->where('expiration', '<', time())
            ->delete();
    }

    /**
     * Delete old Livewire temporary upload files.
     *
     * Survey submissions are guaranteed to finish in less than 24 hours,
     * so it is safe to delete files older than that
     * without affecting any in-progress uploads.
     */
    private function cleanupLivewireTmp(): void
    {
        $storage = Storage::disk('local');

        /** @var string $path */
        foreach ($storage->files('livewire-tmp') as $path) {
            // @codeCoverageIgnoreStart
            if (! $storage->exists($path)) {
                continue;
            }

            // @codeCoverageIgnoreEnd

            if (now()->subHours(24)->timestamp > $storage->lastModified($path)) {
                $storage->delete($path);
            }
        }
    }
}
