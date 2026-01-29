<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\Oidc\OidcProvider;
use App\Services\Oidc\OidcService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Laravel
        Date::use(CarbonImmutable::class);
        Model::shouldBeStrict();
        Model::unguard();
        Vite::useAggressivePrefetching();

        Event::listen(function (SocialiteWasCalled $event): void {
            $oidcService = $this->app->make(OidcService::class);

            $nativeProviders = ['google', 'github'];
            foreach (array_keys($oidcService->getEnabledProviders()) as $provider) {
                if (in_array($provider, $nativeProviders, true)) {
                    continue;
                }

                $class = 'SocialiteProviders\\'.str($provider)->studly().'\\Provider';

                if (class_exists($class)) {
                    $event->extendSocialite($provider, $class);

                    continue;
                }

                if (config()->boolean('services.oidc.oidc_enabled')) {
                    $event->extendSocialite($provider, OidcProvider::class);
                }
            }
        });
    }
}
