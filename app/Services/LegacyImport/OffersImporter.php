<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Actions\Offer\RelinkOfferPartnersAction;
use App\Models\Offer;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class OffersImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'offers';
    }

    protected function legacyIdColumn(): string
    {
        return 'idoffer_price';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('offer_price');
    }

    protected function importRow(stdClass $row): void
    {
        $partnerField = $this->nullableString($row->partner ?? null);
        $partner = app(RelinkOfferPartnersAction::class)->resolve($partnerField);

        $this->upsertByLegacyId(Offer::class, (int) $row->idoffer_price, [
            'legacy_user_id' => isset($row->user) ? (int) $row->user : null,
            'number' => $this->nullableString($row->number ?? null),
            'partner_name' => $partner?->name ?? $partnerField,
            'partner_id' => $partner?->id,
            'city' => $this->nullableString($row->city ?? null),
            'offered_at' => $row->date ?? null,
            'state' => isset($row->state) ? (int) $row->state : null,
            'created_by_legacy_user_id' => isset($row->created_user_id) ? (int) $row->created_user_id : null,
        ]);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}
