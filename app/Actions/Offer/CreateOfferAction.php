<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Models\Offer;
use App\Models\Partner;

final class CreateOfferAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): Offer
    {
        $partnerName = $data['partner_name'] ?? null;
        if (! $partnerName && ! empty($data['partner_id'])) {
            $partnerName = Partner::query()->whereKey($data['partner_id'])->value('name');
        }

        $offer = Offer::query()->create([
            'number' => $data['number'],
            'partner_id' => $data['partner_id'] ?? null,
            'partner_name' => $partnerName,
            'city' => $data['city'] ?? null,
            'offered_at' => $data['offered_at'] ?? now(),
            'valuation_request_id' => $data['valuation_request_id'] ?? null,
            'state' => Offer::STATE_WAITING,
        ]);

        activity('Offer')
            ->performedOn($offer)
            ->withProperties(['action' => 'created'])
            ->log('Offer created');

        return $offer;
    }
}
