<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): Renderable
    {
        return view('home');
    }

    public function underMaintenance(): View
    {
        return view('page-maintenance');
    }

    public function pageComingSoon(): View
    {
        return view('page-coming-soon');
    }
}
