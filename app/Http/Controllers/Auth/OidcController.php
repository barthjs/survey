<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Services\Oidc\OidcService;
use Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Throwable;

final readonly class OidcController
{
    public function redirect(string $provider, OidcService $oidcService): RedirectResponse
    {
        $provider = mb_strtolower($provider);

        abort_unless($oidcService->isEnabled($provider), 404);

        Session::put('oidc_return_to', url()->previous());

        $redirectUrl = route('auth.oidc.callback', ['provider' => $provider]);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        return $driver->redirectUrl($redirectUrl)->redirect();
    }

    public function callback(Request $request, string $provider, OidcService $oidcService): RedirectResponse
    {
        $provider = mb_strtolower($provider);
        /** @var string $returnTo */
        $returnTo = Session::pull('oidc_return_to', route('login'));

        if ($request->has('error')) {
            Session::flash('oidc_error', __('Authentication via :Provider failed.', ['provider' => $provider]));

            return redirect($returnTo);
        }

        abort_unless($oidcService->isEnabled($provider), 404);

        $redirectUrl = route('auth.oidc.callback', ['provider' => $provider]);
        /** @var AbstractProvider $driver */
        $driver = Socialite::driver($provider);

        try {
            /** @var SocialiteUser $socialiteUser */
            $socialiteUser = $driver->redirectUrl($redirectUrl)->user();

            if (auth()->check()) {
                /** @var User $user */
                $user = auth()->user();
                $oidcService->linkProvider($user, $provider, $socialiteUser);

                return redirect(route('profile'));
            }

            $user = $oidcService->handleCallback($provider, $socialiteUser);
            Auth::login($user, remember: true);

            return redirect()->intended(route('surveys.index'));
        } catch (Throwable $e) {
            Session::flash('oidc_error', __('Authentication via :Provider failed.', ['provider' => $provider]));
            Log::error("OIDC Login failed for provider $provider: ".$e->getMessage());

            return redirect($returnTo);
        }
    }
}
