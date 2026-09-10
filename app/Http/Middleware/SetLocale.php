<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', config('app.locale'));

        if (! in_array($locale, ['ar', 'en'])) {
            $locale = 'ar';
        }

        App::setLocale($locale);
        Carbon::setLocale($locale);

        $dir = session('direction');
        if (! in_array($dir, ['rtl', 'ltr'], true)) {
            $dir = $locale === 'ar' ? 'rtl' : 'ltr';
        }

        // Keep language and direction aligned when locale alone changed.
        if ($locale === 'ar') {
            $dir = 'rtl';
        } elseif ($locale === 'en') {
            $dir = 'ltr';
        }

        session([
            'locale' => $locale,
            'direction' => $dir,
        ]);

        config(['custom.custom.direction' => $dir]);

        View::share('currentLocale', $locale);
        View::share('currentDir', $dir);
        View::share('isRtl', $dir === 'rtl');
        View::share('dir', $dir);

        return $next($request);
    }
}
