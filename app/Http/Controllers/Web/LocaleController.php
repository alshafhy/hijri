<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_if(! in_array($locale, ['ar', 'en'], true), 404);

        $direction = $locale === 'ar' ? 'rtl' : 'ltr';

        session([
            'locale' => $locale,
            'direction' => $direction,
        ]);

        // Keep config helper consumers in sync for this request/session.
        config(['custom.custom.direction' => $direction]);

        return redirect()->back()->withHeaders([
            'Cache-Control' => 'no-store, no-cache',
        ]);
    }
}
