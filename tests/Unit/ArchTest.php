<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel()->ignoring([
    'App\Http\Controllers',
]);
arch()->preset()->security()->ignoring(['md5', 'sha1']);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->toHaveSuffix('Controller')
    ->not->toBeUsed();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes()
    ->classes()->toBeFinal();
