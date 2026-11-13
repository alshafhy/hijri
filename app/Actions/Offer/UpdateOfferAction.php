<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Models\Offer;
use App\Models\Partner;

final class UpdateOfferAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Offer $offer, array $data): Offer
    {
        $partnerName = $data['partner_name'] ?? null;
        if (! $partnerName && ! empty($data['partner_id'])) {
            $partnerName = Partner::query()->whereKey($data['partner_id'])->value('name');
        }

        $offer->fill([
            'number' => $data['number'],
            'partner_id' => $data['partner_id'] ?? null,
            'partner_name' => $partnerName,
            'city' => $data['city'] ?? null,
            'offered_at' => $data['offered_at'] ?? $offer->offered_at,
            'valuation_request_id' => $data['valuation_request_id'] ?? null,
        ])->save();

        return $offer->refresh();
    }
}
