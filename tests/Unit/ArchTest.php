<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;

arch()->preset()->php();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring(['md5', 'sha1']);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes();

it('has Model::shouldBeStrict enabled', function () {
    $this->assertTrue(Model::preventsLazyLoading());
    $this->assertTrue(Model::preventsSilentlyDiscardingAttributes());
    $this->assertTrue(Model::preventsAccessingMissingAttributes());
});
