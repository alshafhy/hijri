<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Valuation\ApproveValuationRequestAction;
use App\Actions\Valuation\BuildValuationMapAction;
use App\Actions\Valuation\CancelValuationRequestAction;
use App\Actions\Valuation\ChangeCoordinatorAction;
use App\Actions\Valuation\ChangeEvaluatorAction;
use App\Actions\Valuation\ChangePropertyTypeAction;
use App\Actions\Valuation\CreateValuationRequestAction;
use App\Actions\Valuation\DeletePropertyPictureAction;
use App\Actions\Valuation\DownloadAttachmentsZipAction;
use App\Actions\Valuation\DuplicateValuationRequestAction;
use App\Actions\Valuation\ListQimaNotUploadedAction;
use App\Actions\Valuation\ListSoftDeletedValuationsAction;
use App\Actions\Valuation\ListValuationActivityLogsAction;
use App\Actions\Valuation\MarkEvaluatedAction;
use App\Actions\Valuation\MarkUnderEvaluationAction;
use App\Actions\Valuation\OverrideTotalAmountManualAction;
use App\Actions\Valuation\QuickSearchValuationAction;
use App\Actions\Valuation\RejectValuationRequestAction;
use App\Actions\Valuation\ResolveValuationFinalAmountAction;
use App\Actions\Valuation\RestoreValuationRequestAction;
use App\Actions\Valuation\SearchValuationRequestsAction;
use App\Actions\Valuation\SendValuationRequestAction;
use App\Actions\Valuation\ToggleQimaUploadedStatusAction;
use App\Actions\Valuation\UnapproveValuationRequestAction;
use App\Actions\Valuation\UpdateCoordinatorValuationAction;
use App\Actions\Valuation\UpdateEvaluatorDataAction;
use App\Actions\Valuation\UpdateFeeSharesAction;
use App\Actions\Valuation\UploadOfficialQimaReportAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Valuation\AdvancedSearchValuationRequest;
use App\Http\Requests\Valuation\ChangeCoordinatorRequest;
use App\Http\Requests\Valuation\ChangeEvaluatorRequest;
use App\Http\Requests\Valuation\ChangePropertyTypeRequest;
use App\Http\Requests\Valuation\OverrideAmountRequest;
use App\Http\Requests\Valuation\StoreValuationRequestRequest;
use App\Http\Requests\Valuation\ToggleQimaStatusRequest;
use App\Http\Requests\Valuation\UpdateCoordinatorValuationRequest;
use App\Http\Requests\Valuation\UpdateEvaluatorDataRequest;
use App\Http\Requests\Valuation\UpdateFeeSharesRequest;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Partner;
use App\Models\PropertyPicture;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationBreakdown;
use App\Support\Valuation\ValuationFieldGrid;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use InvalidArgumentException;
use RuntimeException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ValuationRequestController extends Controller
{
    public function index(Request $httpRequest, SearchValuationRequestsAction $search): View
    {
        $this->authorize('viewAny', ValuationRequest::class);

        $filters = $httpRequest->only([
            'q', 'state', 'uploaded_on_qima', 'evaluator_user_id', 'coordinator_user_id', 'sort', 'dir',
        ]);

        $requests = $search->execute($httpRequest->user(), $filters);

        return view('valuation_requests.index', [
            'requests' => $requests,
            'filters' => $filters,
            'evaluators' => $this->usersForRole('evaluator')->union($this->usersForRole('manager'))->sort(),
            'coordinators' => $this->usersForRole('coordinator'),
            'sort' => $filters['sort'] ?? 'id',
            'dir' => $filters['dir'] ?? 'desc',
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', ValuationRequest::class);

        $company = Company::query()->orderBy('id')->first();

        return view('valuation_requests.create', array_merge($this->formLookups(), [
            'contractors' => Contractor::query()->where('state', Contractor::STATE_ACTIVE)->orderBy('name')->pluck('name', 'id'),
            'partners' => Partner::query()->where('state', Partner::STATE_ACTIVE)->orderBy('name')->pluck('name', 'id'),
            'kinds' => ValuationFieldGrid::kinds(),
            'defaultShares' => [
                'coordinator' => (int) ($company?->default_coordinator_share ?? 0),
                'evaluator' => (int) ($company?->default_evaluator_share ?? 0),
                'manager' => (int) ($company?->default_manager_share ?? 0),
            ],
        ]));
    }

    public function store(
        StoreValuationRequestRequest $httpRequest,
        CreateValuationRequestAction $action
    ): RedirectResponse {
        $valuation = $action->execute($httpRequest->user(), $httpRequest->validated());

        return redirect()
            ->route('dashboard.valuation-requests.show', $valuation)
            ->with('success', __('Valuation request created.'));
    }

    public function show(
        ValuationRequest $valuationRequest,
        ResolveValuationFinalAmountAction $resolveAmount
    ): View {
        $this->authorize('view', $valuationRequest);
        $valuationRequest->load([
            'property.location.city',
            'property.location.neighborhood',
            'property.pictures',
            'property.components',
            'property.comparables',
            'property.adjustments',
            'property.total',
            'property.lands',
            'property.facades',
            'property.services',
            'property.borders',
            'property.building',
            'property.question',
            'property.ac',
            'property.flooring',
            'attachments',
            'customTableRows',
            'feeShares',
            'contracts.contractor',
            'partnerContracts.partner',
            'partnerContracts.offerEstate.offer',
            'offers.partner',
            'coordinator',
            'evaluator',
        ]);
        $property = $valuationRequest->property;

        return view('valuation_requests.show', [
            'request' => $valuationRequest,
            'property' => $property,
            'finalAmount' => $resolveAmount->execute($valuationRequest),
            'breakdown' => $property !== null ? ValuationBreakdown::forProperty($property) : null,
            'isBuilding' => $property !== null && ValuationFieldGrid::isBuilding((string) $property->property_kind),
            'kinds' => ValuationFieldGrid::kinds(),
            'timeline' => $this->timeline($valuationRequest),
            'evaluators' => $this->usersForRole('evaluator')->union($this->usersForRole('manager'))->sort(),
            'coordinators' => $this->usersForRole('coordinator'),
        ]);
    }

    /**
     * @return list<array{label: string, at: Carbon|null, duration: string|null}>
     */
    private function timeline(ValuationRequest $request): array
    {
        $steps = [
            ['label' => __('Started at'), 'at' => $request->started_at],
            ['label' => __('Under evaluation'), 'at' => $request->under_evaluation_at],
            ['label' => __('Evaluation completed'), 'at' => $request->evaluated_at],
            ['label' => __('Final approval'), 'at' => $request->ended_at],
        ];

        $previous = null;
        foreach ($steps as $i => $step) {
            $steps[$i]['duration'] = $previous !== null && $step['at'] !== null
                ? $previous->diffForHumans($step['at'], ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2])
                : null;
            $previous = $step['at'] ?? $previous;
        }

        return $steps;
    }

    public function edit(ValuationRequest $valuationRequest): View
    {
        $this->authorize('editInitialData', $valuationRequest);
        $valuationRequest->load(['property.location', 'property.borders', 'attachments', 'feeShares']);
        $cityId = $valuationRequest->property?->location?->city_id;

        return view('valuation_requests.edit', array_merge($this->formLookups(), [
            'request' => $valuationRequest,
            'neighborhoods' => $cityId
                ? GeoNeighborhood::query()->where('city_id', $cityId)->orderBy('name_ar')->pluck('name_ar', 'id')
                : collect(),
        ]));
    }

    public function update(
        UpdateCoordinatorValuationRequest $httpRequest,
        ValuationRequest $valuationRequest,
        UpdateCoordinatorValuationAction $action
    ): RedirectResponse {
        $action->execute($httpRequest->user(), $valuationRequest, $httpRequest->validated());

        return redirect()
            ->route('dashboard.valuation-requests.show', $valuationRequest)
            ->with('success', __('Coordinator fields updated.'));
    }

    public function editInfo(ValuationRequest $valuationRequest): View
    {
        $this->authorize('update', $valuationRequest);
        $valuationRequest->load([
            'property.location', 'property.components', 'property.total', 'property.lands', 'property.facades',
            'property.services', 'property.flooring', 'property.building', 'property.ac', 'property.question',
            'property.comparables', 'property.adjustments', 'property.pictures', 'attachments', 'customTableRows',
        ]);
        $property = $valuationRequest->property;
        abort_if($property === null, 404);

        $cityId = $property->location?->city_id;

        return view('valuation_requests.edit_info', [
            'request' => $valuationRequest,
            'property' => $property,
            'components' => $property->components->keyBy('component_key'),
            'gridRows' => ValuationFieldGrid::rows($property->property_kind, $property->property_type),
            'isBuilding' => ValuationFieldGrid::isBuilding($property->property_kind),
            'cities' => GeoCity::query()->orderBy('name_ar')->pluck('name_ar', 'id'),
            'neighborhoods' => $cityId
                ? GeoNeighborhood::query()->where('city_id', $cityId)->orderBy('name_ar')->pluck('name_ar', 'id')
                : collect(),
        ]);
    }

    public function updateInfo(
        UpdateEvaluatorDataRequest $httpRequest,
        ValuationRequest $valuationRequest,
        UpdateEvaluatorDataAction $action
    ): RedirectResponse {
        $action->execute($httpRequest->user(), $valuationRequest, $httpRequest->validated());

        return redirect()
            ->route('dashboard.valuation-requests.show', $valuationRequest)
            ->with('success', __('Evaluator data saved.'));
    }

    public function send(
        ValuationRequest $valuationRequest,
        SendValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('send', $valuationRequest);

        try {
            $action->execute(request()->user(), $valuationRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Valuation request sent.'));
    }

    public function underEvaluation(
        ValuationRequest $valuationRequest,
        MarkUnderEvaluationAction $action
    ): RedirectResponse {
        $this->authorize('markUnderEvaluation', $valuationRequest);
        $action->execute(request()->user(), $valuationRequest);

        return back()->with('success', __('Marked under evaluation.'));
    }

    public function markEvaluated(
        ValuationRequest $valuationRequest,
        MarkEvaluatedAction $action
    ): RedirectResponse {
        $this->authorize('markEvaluated', $valuationRequest);

        try {
            $action->execute(request()->user(), $valuationRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Marked as evaluated.'));
    }

    public function approve(
        ValuationRequest $valuationRequest,
        ApproveValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('approveFinal', $valuationRequest);

        try {
            $action->execute(request()->user(), $valuationRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Valuation approved.'));
    }

    public function unapprove(
        ValuationRequest $valuationRequest,
        UnapproveValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('unapprove', $valuationRequest);

        try {
            $action->execute(request()->user(), $valuationRequest);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', __('Valuation unapproved.'));
    }

    public function reject(
        ValuationRequest $valuationRequest,
        RejectValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('reject', $valuationRequest);
        $action->execute(request()->user(), $valuationRequest);

        return back()->with('success', __('Valuation rejected back to evaluator.'));
    }

    public function cancel(
        ValuationRequest $valuationRequest,
        CancelValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('cancel', $valuationRequest);
        $action->execute(request()->user(), $valuationRequest);

        return redirect()
            ->route('dashboard.valuation-requests.index')
            ->with('success', __('Valuation cancelled.'));
    }

    public function deleted(ListSoftDeletedValuationsAction $action): View
    {
        $this->authorize('viewDeleted', ValuationRequest::class);
        $requests = $action->execute(request()->user());

        return view('valuation_requests.deleted', compact('requests'));
    }

    public function restore(
        int $valuationRequest,
        RestoreValuationRequestAction $action
    ): RedirectResponse {
        $model = ValuationRequest::onlyTrashed()->findOrFail($valuationRequest);
        $this->authorize('restore', $model);
        $action->execute(request()->user(), $model);

        return redirect()
            ->route('dashboard.valuation-requests.show', $model)
            ->with('success', __('Valuation restored.'));
    }

    public function advancedSearchForm(): View
    {
        $this->authorize('advancedSearch', ValuationRequest::class);

        return view('valuation_requests.advanced_search', array_merge($this->formLookups(), [
            'requests' => null,
            'filters' => [],
        ]));
    }

    public function advancedSearch(
        AdvancedSearchValuationRequest $httpRequest,
        SearchValuationRequestsAction $search
    ): View {
        $filters = $httpRequest->validated();
        $requests = $search->execute($httpRequest->user(), $filters);

        return view('valuation_requests.advanced_search', array_merge($this->formLookups(), [
            'requests' => $requests,
            'filters' => $filters,
        ]));
    }

    public function quickSearch(Request $httpRequest, QuickSearchValuationAction $action): View|RedirectResponse
    {
        $this->authorize('viewAny', ValuationRequest::class);
        $term = (string) $httpRequest->input('q', '');
        $result = $action->execute($httpRequest->user(), $term);

        if (count($result['items']) === 1) {
            return redirect()->route('dashboard.valuation-requests.show', $result['items'][0]['id']);
        }

        return view('valuation_requests.quick_search', [
            'term' => $term,
            'items' => $result['items'],
        ]);
    }

    public function duplicate(
        ValuationRequest $valuationRequest,
        DuplicateValuationRequestAction $action
    ): RedirectResponse {
        $this->authorize('duplicate', $valuationRequest);
        $copy = $action->execute(request()->user(), $valuationRequest);

        return redirect()
            ->route('dashboard.valuation-requests.show', $copy)
            ->with('success', __('Valuation duplicated.'));
    }

    public function downloadAttachmentsZip(
        ValuationRequest $valuationRequest,
        DownloadAttachmentsZipAction $action
    ): StreamedResponse|RedirectResponse {
        $this->authorize('downloadAttachments', $valuationRequest);

        try {
            return $action->execute(request()->user(), $valuationRequest);
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroyPicture(
        ValuationRequest $valuationRequest,
        PropertyPicture $propertyPicture,
        DeletePropertyPictureAction $action
    ): RedirectResponse {
        $this->authorize('deleteAttachment', $valuationRequest);
        $action->execute(request()->user(), $valuationRequest, $propertyPicture);

        return back()->with('success', __('Picture deleted.'));
    }

    public function updateFeeShares(
        UpdateFeeSharesRequest $httpRequest,
        ValuationRequest $valuationRequest,
        UpdateFeeSharesAction $action
    ): RedirectResponse {
        $action->execute($httpRequest->user(), $valuationRequest, $httpRequest->validated());

        return back()->with('success', __('Fee shares updated.'));
    }

    public function barcode(ValuationRequest $valuationRequest): View
    {
        $this->authorize('view', $valuationRequest);

        return view('valuation_requests.barcode', [
            'request' => $valuationRequest,
        ]);
    }

    public function map(Request $httpRequest, BuildValuationMapAction $action): View
    {
        $cityId = $httpRequest->integer('city') ?: null;

        return view('valuation_requests.map', array_merge(
            $action->execute($httpRequest->user(), $cityId),
            ['cityId' => $cityId],
        ));
    }

    public function qimaPending(ListQimaNotUploadedAction $action): View
    {
        $this->authorize('viewAny', ValuationRequest::class);
        $requests = $action->execute(request()->user());

        return view('valuation_requests.qima_pending', compact('requests'));
    }

    public function toggleQima(
        ToggleQimaStatusRequest $httpRequest,
        ValuationRequest $valuationRequest,
        ToggleQimaUploadedStatusAction $action
    ): RedirectResponse {
        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            (bool) $httpRequest->validated('uploaded_on_qima')
        );

        return back()->with('success', __('Qima status updated.'));
    }

    public function activityLogs(Request $httpRequest, ListValuationActivityLogsAction $action): View
    {
        $this->authorize('viewLogs', ValuationRequest::class);
        $valuationId = $httpRequest->integer('valuation_request_id') ?: null;
        $logs = $action->execute($httpRequest->user(), $valuationId);

        return view('valuation_requests.activity_logs', [
            'logs' => $logs,
            'valuationRequestId' => $valuationId,
        ]);
    }

    public function changeEvaluator(
        ChangeEvaluatorRequest $httpRequest,
        ValuationRequest $valuationRequest,
        ChangeEvaluatorAction $action
    ): RedirectResponse {
        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            (int) $httpRequest->validated('evaluator_user_id')
        );

        return back()->with('success', __('Evaluator changed.'));
    }

    public function changeCoordinator(
        ChangeCoordinatorRequest $httpRequest,
        ValuationRequest $valuationRequest,
        ChangeCoordinatorAction $action
    ): RedirectResponse {
        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            (int) $httpRequest->validated('coordinator_user_id')
        );

        return back()->with('success', __('Coordinator changed.'));
    }

    public function changePropertyType(
        ChangePropertyTypeRequest $httpRequest,
        ValuationRequest $valuationRequest,
        ChangePropertyTypeAction $action
    ): RedirectResponse {
        $data = $httpRequest->validated();
        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            $data['property_type'],
            $data['property_kind']
        );

        return back()->with('success', __('Property type changed.'));
    }

    public function overrideAmount(
        OverrideAmountRequest $httpRequest,
        ValuationRequest $valuationRequest,
        OverrideTotalAmountManualAction $action
    ): RedirectResponse {
        $amount = $httpRequest->validated('total_amount_manual');
        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            $amount !== null ? (float) $amount : null
        );

        return back()->with('success', __('Manual amount updated.'));
    }

    public function uploadOfficialReport(
        Request $httpRequest,
        ValuationRequest $valuationRequest,
        UploadOfficialQimaReportAction $action
    ): RedirectResponse {
        $this->authorize('uploadOfficialReport', $valuationRequest);

        $validated = $httpRequest->validate([
            'official_report' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:20480'],
            'report_number' => ['nullable', 'string', 'max:250'],
            'deposit_number' => ['nullable', 'string', 'max:255'],
        ]);

        $action->execute(
            $httpRequest->user(),
            $valuationRequest,
            $httpRequest->file('official_report'),
            $validated['report_number'] ?? null,
            $validated['deposit_number'] ?? null,
        );

        return back()->with('success', __('Official report uploaded and request locked.'));
    }

    /**
     * @return array<string, mixed>
     */
    private function formLookups(): array
    {
        return [
            'evaluators' => $this->usersForRole('evaluator')->union($this->usersForRole('manager'))->sort(),
            'coordinators' => $this->usersForRole('coordinator'),
            'companies' => Company::query()->orderBy('name')->pluck('name', 'id'),
            'cities' => GeoCity::query()->orderBy('name_ar')->pluck('name_ar', 'id'),
        ];
    }

    /**
     * @return Collection<int|string, string>
     */
    private function usersForRole(string $role)
    {
        try {
            return User::query()
                ->role($role)
                ->orderBy('name')
                ->pluck('name', 'id');
        } catch (RoleDoesNotExist) {
            return collect();
        }
    }
}
