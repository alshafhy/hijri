<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Contractor\DeactivatePartyContactAction;
use App\Actions\Partner\ActivatePartnerAction;
use App\Actions\Partner\CreatePartnerAction;
use App\Actions\Partner\DeactivatePartnerAction;
use App\Actions\Partner\UpdatePartnerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Partner\StorePartnerRequest;
use App\Http\Requests\Partner\UpdatePartnerRequest;
use App\Models\Partner;
use App\Models\PartyContact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Partner::class);

        $partners = Partner::query()
            ->withCount('offers')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->string('q')).'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term);
                });
            })
            ->when($request->filled('state'), fn ($q) => $q->where('state', (int) $request->input('state')))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('partners.index', compact('partners'));
    }

    public function create(): View
    {
        $this->authorize('create', Partner::class);

        return view('partners.create');
    }

    public function store(StorePartnerRequest $request, CreatePartnerAction $action): RedirectResponse
    {
        $partner = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/partners.singular')]));

        return redirect()->route('dashboard.partners.show', $partner);
    }

    public function show(Partner $partner): View
    {
        $this->authorize('view', $partner);

        $partner->load([
            'offers' => fn ($q) => $q->latest('id')->limit(50),
            'contacts' => fn ($q) => $q->latest('id')->limit(50),
        ]);

        return view('partners.show', compact('partner'));
    }

    public function deactivateContact(
        Partner $partner,
        PartyContact $contact,
        DeactivatePartyContactAction $action
    ): RedirectResponse {
        abort_unless($contact->partner_id === $partner->id, 404);

        $action(request()->user(), $contact);

        Flash::success(__('Contact removed'));

        return back();
    }

    public function edit(Partner $partner): View
    {
        $this->authorize('update', $partner);

        return view('partners.edit', compact('partner'));
    }

    public function update(
        UpdatePartnerRequest $request,
        Partner $partner,
        UpdatePartnerAction $action
    ): RedirectResponse {
        $action($partner, $request->validated());

        Flash::success(__('messages.updated', ['model' => __('models/partners.singular')]));

        return redirect()->route('dashboard.partners.show', $partner);
    }

    public function destroy(Partner $partner, DeactivatePartnerAction $action): RedirectResponse
    {
        $this->authorize('delete', $partner);

        $action($partner);

        Flash::success(__('messages.deleted', ['model' => __('models/partners.singular')]));

        return redirect()->route('dashboard.partners.index');
    }

    public function activate(Partner $partner, ActivatePartnerAction $action): RedirectResponse
    {
        $this->authorize('activate', $partner);

        $action($partner);

        Flash::success(__('Partner activated'));

        return back();
    }

    public function deactivate(Partner $partner, DeactivatePartnerAction $action): RedirectResponse
    {
        $this->authorize('activate', $partner);

        $action($partner);

        Flash::success(__('Partner deactivated'));

        return redirect()->route('dashboard.partners.index');
    }
}
