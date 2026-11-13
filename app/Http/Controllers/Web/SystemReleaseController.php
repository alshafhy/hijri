<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SystemRelease;
use Illuminate\Contracts\View\View;

final class SystemReleaseController extends Controller
{
    public function index(): View
    {
        $releases = SystemRelease::query()
            ->with(['features' => fn ($q) => $q->orderBy('feature_order')])
            ->orderByDesc('release_date')
            ->orderByDesc('id')
            ->get();

        return view('pages.releases', compact('releases'));
    }
}
