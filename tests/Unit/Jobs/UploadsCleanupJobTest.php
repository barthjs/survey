<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;
use Illuminate\Support\Facades\Storage;

beforeEach(function (): void {
    Storage::fake('local');
});

it('deletes specified files and their empty directories', function (): void {
    Storage::disk('local')->put('file1.txt', 'content');
    Storage::disk('local')->put('dir/file2.txt', 'content');

    expect(Storage::disk('local')->exists('file1.txt'))->toBeTrue()
        ->and(Storage::disk('local')->exists('dir/file2.txt'))->toBeTrue();

    $job = new UploadsCleanupJob([
        'file1.txt',
        'dir/file2.txt',
    ]);
    $job->handle();

    expect(Storage::disk('local')->exists('uploads/file1.txt'))->toBeFalse()
        ->and(Storage::disk('local')->exists('uploads/dir/file2.txt'))->toBeFalse()
        ->and(Storage::disk('local')->exists('uploads/dir'))->toBeFalse();
});
