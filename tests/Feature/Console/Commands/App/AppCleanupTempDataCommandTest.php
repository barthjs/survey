<?php

declare(strict_types=1);

use App\Console\Commands\App\AppCleanupTempDataCommand;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

use function Pest\Laravel\artisan;

beforeEach(function (): void {
    Storage::fake('local');
});

it('removes expired cache entries while keeping fresh ones', function (): void {
    DB::table(config()->string('cache.stores.database.table'))->insert([
        ['key' => 'expired', 'value' => 'a', 'expiration' => now()->subMinute()->timestamp],
        ['key' => 'fresh', 'value' => 'b', 'expiration' => now()->addHour()->timestamp],
    ]);

    DB::table(config()->string('cache.stores.database.lock_table'))->insert([
        ['key' => 'lock-expired', 'owner' => 'x', 'expiration' => now()->subMinute()->timestamp],
        ['key' => 'lock-fresh', 'owner' => 'y', 'expiration' => now()->addHour()->timestamp],
    ]);

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(DB::table(config()->string('cache.stores.database.table')))->pluck('key')->all()->toBe(['fresh'])
        ->and(DB::table(config()->string('cache.stores.database.lock_table'))->pluck('key')->all())->toBe(['lock-fresh']);
});

it('deletes livewire-tmp files older than 24 hours and keeps recent ones', function (): void {
    $disk = Storage::disk('local');

    $disk->put('livewire-tmp/old.txt', 'old');
    $disk->put('livewire-tmp/recent.txt', 'recent');

    touch($disk->path('livewire-tmp/old.txt'), now()->subHours(25)->timestamp);
    touch($disk->path('livewire-tmp/recent.txt'), now()->subHours(1)->timestamp);

    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect($disk->exists('livewire-tmp/old.txt'))->toBeFalse()
        ->and($disk->exists('livewire-tmp/recent.txt'))->toBeTrue();
});

it('succeeds when there is nothing to clean up', function (): void {
    artisan(AppCleanupTempDataCommand::class)->assertSuccessful();

    expect(DB::table(config()->string('cache.stores.database.table')))->count()->toBe(0)
        ->and(DB::table(config()->string('cache.stores.database.lock_table'))->count())->toBe(0)
        ->and(Storage::disk('local')->files('livewire-tmp'))->toBeEmpty();
});
