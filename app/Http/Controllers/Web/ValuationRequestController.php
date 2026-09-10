<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Valuation\UploadOfficialQimaReportAction;
use App\Http\Controllers\Controller;
use App\Models\ValuationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ValuationRequestController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', ValuationRequest::class);

        $requests = ValuationRequest::query()
            ->with('property')
            ->latest('id')
            ->paginate(25);

        return view('valuation_requests.index', compact('requests'));
    }

    public function show(ValuationRequest $valuationRequest): View
    {
        $this->authorize('view', $valuationRequest);
        $valuationRequest->load(['property.location', 'property.pictures', 'feeShares']);

        return view('valuation_requests.show', ['request' => $valuationRequest]);
    }

    public function uploadOfficialReport(
        Request $httpRequest,
        ValuationRequest $valuationRequest,
        UploadOfficialQimaReportAction $action
    ): RedirectResponse {
        $this->authorize('uploadOfficialReport', $valuationRequest);

        $httpRequest->validate([
            'official_report' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
        ]);

        $action->execute($httpRequest->user(), $valuationRequest, $httpRequest->file('official_report'));

        return back()->with('success', __('Official report uploaded and request locked.'));
    }
}
