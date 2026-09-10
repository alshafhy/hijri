<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ImportQuarantine;
use App\Models\Property;
use App\Models\PropertyPicture;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): Renderable
    {
        $user = Auth::user();
        abort_unless($user !== null && $user->can('dashboard.view'), 403);

        $widgets = [
            'total_requests' => ValuationRequest::query()->count(),
            'linked_properties' => Property::query()->whereNotNull('valuation_request_id')->count(),
            'qima_uploaded' => ValuationRequest::query()->where('uploaded_on_qima', true)->count(),
            'pending_evaluation' => ValuationRequest::query()
                ->whereNull('evaluated_at')
                ->where('uploaded_on_qima', false)
                ->count(),
            'quarantine' => ImportQuarantine::query()->count(),
            'pictures_missing' => PropertyPicture::query()->where('file_exists', false)->count(),
            'pictures_total' => PropertyPicture::query()->count(),
        ];

        if ($user->hasRole('evaluator')) {
            $widgets['my_assigned'] = ValuationRequest::query()
                ->where('evaluator_user_id', $user->id)
                ->count();
        }

        if ($user->hasRole('coordinator')) {
            $widgets['my_coordinated'] = ValuationRequest::query()
                ->where('coordinator_user_id', $user->id)
                ->count();
        }

        return view('home', [
            'widgets' => $widgets,
            'roles' => $user->getRoleNames(),
        ]);
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
