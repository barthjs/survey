<?php

declare(strict_types=1);

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

arch()->preset()->php();

arch()->preset()->laravel();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes();

it('has Model::shouldBeStrict enabled', function () {
    $this->assertTrue(Model::preventsLazyLoading());
    $this->assertTrue(Model::preventsSilentlyDiscardingAttributes());
    $this->assertTrue(Model::preventsAccessingMissingAttributes());
});

it('checks if all HTTP controllers extend the base controller', function () {
    $this->expect('App\Http\Controllers')->toExtend(Controller::class);
});
