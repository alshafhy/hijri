<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Enums\Commercial\OfferEstateStatus;
use App\Models\OfferEstate;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

final class ActivateOfferEstateAction
{
    public function execute(User $actor, OfferEstate $estate, bool $active = true): OfferEstate
    {
        Gate::forUser($actor)->authorize('update', $estate->offer);

        $estate->forceFill([
            'status' => $active ? OfferEstateStatus::Active : OfferEstateStatus::Inactive,
        ])->save();

        activity('OfferEstate')
            ->performedOn($estate)
            ->causedBy($actor)
            ->withProperties(['event' => $active ? 'activated' : 'deactivated'])
            ->log($active ? 'Offer estate activated' : 'Offer estate deactivated');

        return $estate->refresh();
    }
}
