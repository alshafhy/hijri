<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    public function swap(string $locale): RedirectResponse
    {
        $availLocale = ['ar' => 'ar', 'en' => 'en'];

        abort_if(! array_key_exists($locale, $availLocale), 404);

        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        session([
            'locale' => $locale,
            'direction' => $direction,
        ]);

        return redirect()->back()->withHeaders([
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
