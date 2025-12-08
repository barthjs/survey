<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    /**
     * Set the app locale based on the session or browser preferences.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $availableLocales = config('app.locales');

        if (session()->has('locale') && array_key_exists(session('locale'), $availableLocales)) {
            app()->setLocale(session('locale'));
        } else {
            // Get the first language from the Accept-Language header
            $requestLocale = $request->getPreferredLanguage() ?? '';
            $locale = mb_strtolower(mb_substr($requestLocale, 0, 2));

            if (array_key_exists($locale, $availableLocales)) {
                app()->setLocale($locale);

                return $next($request);
            }

            app()->setLocale(config('app.locale'));
        }

        return $next($request);
    }
}
