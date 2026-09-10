<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Offer;
use App\Models\Partner;
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
        $partnerName = $this->nullableString($row->partner ?? null);
        $partnerId = null;
        if ($partnerName !== null) {
            $partnerId = Partner::query()->where('name', $partnerName)->value('id');
        }

        $this->upsertByLegacyId(Offer::class, (int) $row->idoffer_price, [
            'legacy_user_id' => isset($row->user) ? (int) $row->user : null,
            'number' => $this->nullableString($row->number ?? null),
            'partner_name' => $partnerName,
            'partner_id' => $partnerId,
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
