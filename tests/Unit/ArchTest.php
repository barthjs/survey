<?php

declare(strict_types=1);

arch()->preset()->php();
arch()->preset()->laravel();
arch()->preset()->security()->ignoring(['md5', 'sha1']);

arch('controllers')
    ->expect('App\Http\Controllers')
    ->not->toBeUsed();

arch('strict mode')
    ->expect('App')
    ->toUseStrictEquality()
    ->toUseStrictTypes()
    ->classes()->toBeFinal();
