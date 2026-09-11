<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Models\Offer;

final class DeactivateOfferAction
{
    public function __invoke(Offer $offer): Offer
    {
        $offer->forceFill(['state' => Offer::STATE_REJECTED])->save();

        activity('Offer')
            ->performedOn($offer)
            ->withProperties(['action' => 'deactivated'])
            ->log('Offer rejected');

        return $offer->refresh();
    }
}
