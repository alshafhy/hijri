<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Contractor\ActivateContractorAction;
use App\Actions\Contractor\CreateContractorAction;
use App\Actions\Contractor\CreatePartyContactAction;
use App\Actions\Contractor\DeactivateContractorAction;
use App\Actions\Contractor\DeactivatePartyContactAction;
use App\Actions\Contractor\UpdateContractorAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contractor\StoreContractorRequest;
use App\Http\Requests\Contractor\StorePartyContactRequest;
use App\Http\Requests\Contractor\UpdateContractorRequest;
use App\Models\Company;
use App\Models\Contractor;
use App\Models\PartyContact;
use App\Support\Query\AppliesListSort;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class ContractorController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Contractor::class);

        $query = Contractor::query()
            ->withCount('contracts')
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.trim((string) $request->string('q')).'%';
                $builder->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                });
            })
            ->when($request->filled('state'), fn ($q) => $q->where('state', (int) $request->input('state')));

        AppliesListSort::apply($query, $request, ['id', 'name', 'email', 'phone_number', 'fees', 'state', 'created_at']);

        $contractors = $query->paginate(25)->withQueryString();
        $sortMeta = AppliesListSort::current($request);

        return view('contractors.index', compact('contractors') + $sortMeta);
    }

    public function create(): View
    {
        $this->authorize('create', Contractor::class);

        return view('contractors.create');
    }

    public function store(StoreContractorRequest $request, CreateContractorAction $action): RedirectResponse
    {
        $contractor = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/contractors.singular')]));

        return redirect()->route('dashboard.contractors.edit', $contractor);
    }

    public function show(Contractor $contractor): View
    {
        $this->authorize('view', $contractor);

        $contracts = $contractor->contracts()
            ->with(['valuationRequest' => fn ($query) => $query->with('property.location.city', 'property.location.neighborhood')])
            ->latest('id')
            ->paginate(25);

        $contacts = $contractor->contacts()
            ->latest('id')
            ->paginate(10, ['*'], 'contacts_page');

        return view('contractors.show', compact('contractor', 'contracts', 'contacts'));
    }

    public function invoice(Contractor $contractor): Response
    {
        $this->authorize('view', $contractor);

        $contracts = $contractor->contracts()
            ->with(['valuationRequest' => fn ($query) => $query->with('property.location.city', 'property.location.neighborhood')])
            ->oldest('id')
            ->get();

        $fees = (float) $contractor->fees;
        $paidCount = $contracts->filter->isPaid()->count();

        return Pdf::loadView('contractors.invoice', [
            'contractor' => $contractor,
            'company' => Company::query()->orderBy('id')->first(),
            'contracts' => $contracts,
            'fees' => $fees,
            'totals' => [
                'all' => $fees * $contracts->count(),
                'paid' => $fees * $paidCount,
                'unpaid' => $fees * ($contracts->count() - $paidCount),
            ],
            'issuedAt' => now(),
        ])->setPaper('a4')->setOption('defaultFont', 'DejaVu Sans')->stream('invoice-'.$contractor->id.'.pdf');
    }

    public function storeContact(
        StorePartyContactRequest $request,
        Contractor $contractor,
        CreatePartyContactAction $action
    ): RedirectResponse {
        $action->execute($request->user(), $contractor, $request->validated());

        Flash::success(__('Contact added'));

        return back();
    }

    public function deactivateContact(
        Contractor $contractor,
        PartyContact $contact,
        DeactivatePartyContactAction $action
    ): RedirectResponse {
        abort_unless($contact->contractor_id === $contractor->id, 404);

        $action->execute(request()->user(), $contact);

        Flash::success(__('Contact removed'));

        return back();
    }

    public function edit(Contractor $contractor): View
    {
        $this->authorize('update', $contractor);

        return view('contractors.edit', compact('contractor'));
    }

    public function update(
        UpdateContractorRequest $request,
        Contractor $contractor,
        UpdateContractorAction $action
    ): RedirectResponse {
        $action($contractor, $request->validated());

        Flash::success(__('messages.updated', ['model' => __('models/contractors.singular')]));

        return redirect()->route('dashboard.contractors.show', $contractor);
    }

    public function destroy(Contractor $contractor, DeactivateContractorAction $action): RedirectResponse
    {
        $this->authorize('delete', $contractor);

        $action($contractor);

        Flash::success(__('messages.deleted', ['model' => __('models/contractors.singular')]));

        return redirect()->route('dashboard.contractors.index');
    }

    public function activate(Contractor $contractor, ActivateContractorAction $action): RedirectResponse
    {
        $this->authorize('activate', $contractor);

        $action($contractor);

        Flash::success(__('Contractor activated'));

        return back();
    }

    public function deactivate(Contractor $contractor, DeactivateContractorAction $action): RedirectResponse
    {
        $this->authorize('activate', $contractor);

        $action($contractor);

        Flash::success(__('Contractor deactivated'));

        return redirect()->route('dashboard.contractors.index');
    }
}
