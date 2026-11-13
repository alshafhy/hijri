<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class PartnerContractsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'partner_contracts';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('contract2');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $legacyPartnerId = (int) ($row->partner ?? 0) ?: null;
        $legacyEstateId = (int) ($row->estate ?? 0) ?: null;
        $legacyRequestId = (int) ($row->request ?? 0) ?: null;

        $requestId = $legacyRequestId ? $this->valuationRequestIdByLegacy($legacyRequestId) : null;
        if ($requestId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'request_not_imported');

            return;
        }

        $this->upsertByLegacyId(PartnerContract::class, $legacyId, [
            'partner_id' => $legacyPartnerId ? Partner::query()->where('legacy_id', $legacyPartnerId)->value('id') : null,
            'offer_estate_id' => $legacyEstateId ? OfferEstate::query()->where('legacy_id', $legacyEstateId)->value('id') : null,
            'valuation_request_id' => $requestId,
            'legacy_partner_id' => $legacyPartnerId,
            'legacy_estate_id' => $legacyEstateId,
            'legacy_request_id' => $legacyRequestId,
            'state' => (int) ($row->state ?? 0),
        ]);
    }
}
