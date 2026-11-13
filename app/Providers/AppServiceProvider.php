<?php

namespace App\Providers;

use App\Contracts\ReportPdfRenderer;
use App\Http\ViewComposers\MenuComposer;
use App\Observers\PermissionObserver;
use App\Support\Pdf\MpdfReportRenderer;
use App\Support\Valuation\ValuationSla;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->scoped(ValuationSla::class);
        $this->app->bind(ReportPdfRenderer::class, MpdfReportRenderer::class);
    }

    public function boot(): void
    {
        if (app()->environment('production') || config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrapFive();

        View::composer('panels.sidebar', MenuComposer::class);

        Permission::observe(PermissionObserver::class);
        Role::observe(PermissionObserver::class);
    }
}
