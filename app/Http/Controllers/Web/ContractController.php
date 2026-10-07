<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Contract\CreateContractAction;
use App\Actions\Contract\MarkContractPaidAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contract\StoreContractRequest;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\ValuationRequest;
use App\Support\Query\AppliesListSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contract::class);

        $query = Contract::query()
            ->with([
                'contractor:id,name,fees',
                'valuationRequest:id,number,reference,state',
            ])
            ->when($request->filled('state'), fn ($q) => $q->where('state', (int) $request->input('state')))
            ->when($request->filled('contractor_id'), fn ($q) => $q->where('contractor_id', (int) $request->input('contractor_id')));

        AppliesListSort::apply($query, $request, ['id', 'contractor_id', 'valuation_request_id', 'state', 'created_at']);

        $contracts = $query->paginate(25)->withQueryString();
        $contractors = Contractor::query()->orderBy('name')->pluck('name', 'id');
        $sortMeta = AppliesListSort::current($request);

        return view('contracts.index', compact('contracts', 'contractors') + $sortMeta);
    }

    public function create(): View
    {
        $this->authorize('create', Contract::class);

        $contractors = Contractor::query()->orderBy('name')->pluck('name', 'id');
        $valuationRequests = ValuationRequest::query()
            ->latest('id')
            ->limit(500)
            ->get(['id', 'number', 'reference'])
            ->mapWithKeys(fn (ValuationRequest $r) => [
                $r->id => trim(($r->number ?: '#'.$r->id).' / '.($r->reference ?: '—')),
            ]);

        return view('contracts.create', compact('contractors', 'valuationRequests'));
    }

    public function store(StoreContractRequest $request, CreateContractAction $action): RedirectResponse
    {
        $contract = $action->execute($request->user(), $request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/contracts.singular')]));

        return redirect()->route('dashboard.contracts.show', $contract);
    }

    public function show(Contract $contract): View
    {
        $this->authorize('view', $contract);

        $contract->load([
            'contractor',
            'valuationRequest.property',
            'valuationRequest.feeShares',
        ]);

        return view('contracts.show', compact('contract'));
    }

    public function markPaid(Contract $contract, MarkContractPaidAction $action): RedirectResponse
    {
        $this->authorize('markPaid', $contract);

        $action($contract);

        Flash::success(__('Contract marked as paid'));

        return back();
    }
}
