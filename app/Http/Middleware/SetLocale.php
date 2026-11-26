<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locales = config('zirak.locales');

        $requested = $request->query('lang');
        if ($requested && isset($locales[$requested])) {
            $request->session()->put('locale', $requested);
            if ($request->user() && $request->user()->locale !== $requested) {
                $request->user()->forceFill(['locale' => $requested])->save();
            }
        }

        $locale = $request->session()->get('locale')
            ?? $request->user()?->locale
            ?? config('zirak.default_locale');

        if (! isset($locales[$locale])) {
            $locale = config('zirak.default_locale');
        }

        app()->setLocale($locale);
        Carbon::setLocale($locales[$locale]['carbon']);

        return $next($request);
    }
}
