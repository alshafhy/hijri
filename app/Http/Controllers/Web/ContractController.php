<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Contract\MarkContractPaidAction;
use App\Http\Controllers\Controller;
use App\Models\Contract;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class ContractController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contract::class);

        $contracts = Contract::query()
            ->with([
                'contractor:id,name,fees',
                'valuationRequest:id,number,reference,state',
            ])
            ->when($request->filled('state'), fn ($q) => $q->where('state', (int) $request->input('state')))
            ->when($request->filled('contractor_id'), fn ($q) => $q->where('contractor_id', (int) $request->input('contractor_id')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('contracts.index', compact('contracts'));
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
