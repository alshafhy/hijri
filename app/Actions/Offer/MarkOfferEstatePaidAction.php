<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\Commercial\OfferEstatePaymentStatus;
use App\Models\OfferEstate;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class MarkOfferEstatePaidAction
{
    public function execute(User $actor, OfferEstate $estate): OfferEstate
    {
        Gate::forUser($actor)->authorize('update', $estate->offer);

        $estate->forceFill([
            'payment_status' => OfferEstatePaymentStatus::Paid,
        ])->save();

        activity('OfferEstate')
            ->performedOn($estate)
            ->causedBy($actor)
            ->withProperties(['event' => 'marked_paid'])
            ->log('Offer estate marked paid');

        return $estate->refresh();
    }
}
