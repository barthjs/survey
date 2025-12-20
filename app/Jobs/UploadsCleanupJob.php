<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

final class UploadsCleanupJob implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<string>  $filePaths
     */
    public function __construct(
        private readonly array $filePaths = []
    ) {}

    public function handle(): void
    {
        $disk = Storage::disk('local');
        foreach ($this->filePaths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }

            $directory = dirname($path);
            if ($disk->exists($directory) && count($disk->allFiles($directory)) === 0) {
                $disk->deleteDirectory($directory);
            }
        }

        $storage = Storage::disk('local');

        /** @var string $path */
        foreach ($storage->files('livewire-tmp') as $path) {
            if (! $storage->exists($path)) {
                continue;
            }

            if (now()->subHours(24)->timestamp > $storage->lastModified($path)) {
                $storage->delete($path);
            }
        }
    }
}
