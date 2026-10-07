<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Offer\ActivateOfferAction;
use App\Actions\Offer\ActivateOfferEstateAction;
use App\Actions\Offer\CreateOfferAction;
use App\Actions\Offer\CreateOfferEstateAction;
use App\Actions\Offer\DeactivateOfferAction;
use App\Actions\Offer\MarkOfferEstatePaidAction;
use App\Actions\Offer\UpdateOfferAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Offer\StoreOfferEstateRequest;
use App\Http\Requests\Offer\StoreOfferRequest;
use App\Http\Requests\Offer\UpdateOfferRequest;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Support\Query\AppliesListSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class OfferController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Offer::class);

        $query = Offer::query()
            ->with(['partner:id,name'])
            ->withCount('estates')
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.trim((string) $request->string('q')).'%';
                $builder->where(function ($q) use ($term): void {
                    $q->where('number', 'like', $term)
                        ->orWhere('partner_name', 'like', $term)
                        ->orWhere('city', 'like', $term);
                });
            })
            ->when($request->filled('state'), fn ($q) => $q->where('state', (int) $request->input('state')))
            ->when($request->filled('partner_id'), fn ($q) => $q->where('partner_id', (int) $request->input('partner_id')));

        AppliesListSort::apply($query, $request, ['id', 'number', 'partner_id', 'city', 'offered_at', 'state', 'created_at']);

        $offers = $query->paginate(25)->withQueryString();
        $partners = Partner::query()->orderBy('name')->pluck('name', 'id');
        $sortMeta = AppliesListSort::current($request);

        return view('offers.index', compact('offers', 'partners') + $sortMeta);
    }

    public function create(): View
    {
        $this->authorize('create', Offer::class);

        $partners = Partner::query()
            ->where('state', Partner::STATE_ACTIVE)
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('offers.create', compact('partners'));
    }

    public function store(StoreOfferRequest $request, CreateOfferAction $action): RedirectResponse
    {
        $offer = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/offers.singular')]));

        return redirect()->route('dashboard.offers.show', $offer);
    }

    public function show(Offer $offer): View
    {
        $this->authorize('view', $offer);

        $offer->load([
            'partner',
            'valuationRequest:id,number,reference,state',
            'estates' => fn ($q) => $q->orderBy('id'),
        ]);

        return view('offers.show', compact('offer'));
    }

    public function storeEstate(
        StoreOfferEstateRequest $request,
        Offer $offer,
        CreateOfferEstateAction $action
    ): RedirectResponse {
        $action->execute($request->user(), $offer, $request->validated());

        Flash::success(__('Estate line added'));

        return back();
    }

    public function markEstatePaid(
        Offer $offer,
        OfferEstate $estate,
        MarkOfferEstatePaidAction $action
    ): RedirectResponse {
        abort_unless($estate->offer_id === $offer->id, 404);

        $action->execute(request()->user(), $estate);

        Flash::success(__('Estate marked as paid'));

        return back();
    }

    public function activateEstate(
        Offer $offer,
        OfferEstate $estate,
        ActivateOfferEstateAction $action
    ): RedirectResponse {
        abort_unless($estate->offer_id === $offer->id, 404);

        $action->execute(request()->user(), $estate, true);

        Flash::success(__('Estate activated'));

        return back();
    }

    public function deactivateEstate(
        Offer $offer,
        OfferEstate $estate,
        ActivateOfferEstateAction $action
    ): RedirectResponse {
        abort_unless($estate->offer_id === $offer->id, 404);

        $action->execute(request()->user(), $estate, false);

        Flash::success(__('Estate deactivated'));

        return back();
    }

    public function edit(Offer $offer): View
    {
        $this->authorize('update', $offer);

        $partners = Partner::query()
            ->where(function ($q) use ($offer): void {
                $q->where('state', Partner::STATE_ACTIVE)
                    ->orWhere('id', $offer->partner_id);
            })
            ->orderBy('name')
            ->pluck('name', 'id');

        return view('offers.edit', compact('offer', 'partners'));
    }

    public function update(
        UpdateOfferRequest $request,
        Offer $offer,
        UpdateOfferAction $action
    ): RedirectResponse {
        $action($offer, $request->validated());

        Flash::success(__('messages.updated', ['model' => __('models/offers.singular')]));

        return redirect()->route('dashboard.offers.show', $offer);
    }

    public function activate(Offer $offer, ActivateOfferAction $action): RedirectResponse
    {
        $this->authorize('activate', $offer);

        $action($offer);

        Flash::success(__('Offer activated'));

        return back();
    }

    public function deactivate(Offer $offer, DeactivateOfferAction $action): RedirectResponse
    {
        $this->authorize('activate', $offer);

        $action($offer);

        Flash::success(__('Offer deactivated'));

        return back();
    }
}
