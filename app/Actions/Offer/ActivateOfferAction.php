<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Models\Offer;

final class ActivateOfferAction
{
    public function __invoke(Offer $offer): Offer
    {
        $offer->forceFill(['state' => Offer::STATE_ACCEPTED])->save();

        activity('Offer')
            ->performedOn($offer)
            ->withProperties(['action' => 'activated'])
            ->log('Offer accepted');

        return $offer->refresh();
    }
}
