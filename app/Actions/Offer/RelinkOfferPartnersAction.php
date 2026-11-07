<?php

declare(strict_types=1);

namespace App\Actions\Offer;

use App\Models\Offer;
use App\Models\Partner;

final class RelinkOfferPartnersAction
{
    /** @return array{linked: int, skipped: int} */
    public function __invoke(): array
    {
        $linked = 0;
        $skipped = 0;

        Offer::query()
            ->whereNull('partner_id')
            ->whereNotNull('partner_name')
            ->orderBy('id')
            ->each(function (Offer $offer) use (&$linked, &$skipped): void {
                $partner = $this->resolve($offer->partner_name);
                if ($partner === null) {
                    $skipped++;

                    return;
                }

                $offer->forceFill([
                    'partner_id' => $partner->id,
                    'partner_name' => $partner->name,
                ])->save();
                $linked++;
            });

        return ['linked' => $linked, 'skipped' => $skipped];
    }

    public function resolve(?string $partnerField): ?Partner
    {
        if ($partnerField === null) {
            return null;
        }

        $value = trim($partnerField);
        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            return Partner::query()->where('legacy_id', (int) $value)->first();
        }

        return Partner::query()
            ->whereRaw('trim(name) = ?', [$value])
            ->first();
    }
}
