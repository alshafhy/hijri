<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\Commercial\OfferEstatePaymentStatus;
use App\Enums\Commercial\OfferEstateStatus;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class CreateOfferEstateAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, Offer $offer, array $data): OfferEstate
    {
        Gate::forUser($actor)->authorize('update', $offer);

        $estate = OfferEstate::query()->create([
            'offer_id' => $offer->id,
            'estate_kind' => $data['estate_kind'] ?? null,
            'estate_type' => $data['estate_type'] ?? null,
            'instrument_no' => $data['instrument_no'] ?? null,
            'area' => isset($data['area']) ? (int) $data['area'] : null,
            'neighborhood' => $data['neighborhood'] ?? null,
            'fees' => isset($data['fees']) ? (int) $data['fees'] : null,
            'payment_status' => OfferEstatePaymentStatus::Unpaid,
            'status' => OfferEstateStatus::Active,
        ]);

        activity('OfferEstate')
            ->performedOn($estate)
            ->causedBy($actor)
            ->withProperties(['action' => 'created', 'offer_id' => $offer->id])
            ->log('Offer estate line created');

        return $estate->refresh();
    }
}
