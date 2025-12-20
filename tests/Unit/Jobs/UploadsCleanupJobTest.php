<?php

declare(strict_types=1);

use App\Jobs\UploadsCleanupJob;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('local');
});

it('deletes specified files and their empty directories', function () {
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

it('deletes files older than 24 hours in livewire-tmp', function () {
    Storage::disk('local')->put('livewire-tmp/old-file.txt', 'old content');
    Storage::disk('local')->put('livewire-tmp/new-file.txt', 'new content');

    Storage::disk('local')->setVisibility('livewire-tmp/old-file.txt', 'public');
    touch(Storage::disk('local')->path('livewire-tmp/old-file.txt'), now()->subHours(25)->timestamp);

    $job = new UploadsCleanupJob([]);
    $job->handle();

    expect(Storage::disk('local')->exists('livewire-tmp/old-file.txt'))->toBeFalse()
        ->and(Storage::disk('local')->exists('livewire-tmp/new-file.txt'))->toBeTrue();
});
