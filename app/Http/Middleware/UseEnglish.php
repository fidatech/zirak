<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** The admin panel is in English, whatever the site's default language (APP_LOCALE=prs in production). */
class UseEnglish
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('en');
        Carbon::setLocale('en');

        return $next($request);
    }
}
