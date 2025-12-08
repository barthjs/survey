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
     * @var array<string>
     */
    protected array $filePaths;

    /**
     * Create a new job instance.
     */
    public function __construct(array $filePaths = [])
    {
        $this->filePaths = $filePaths;
    }

    /**
     * Execute the job.
     */
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

        foreach (Storage::disk('local')->allFiles('livewire-tmp') as $path) {
            if (Storage::lastModified($path) < (time() - 60 * 60 * 24 * 30)) {
                Storage::delete($path);
            }
        }
    }
}
