<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Contract;
use App\Models\Contractor;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class ContractsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'contracts';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('contract');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $legacyContractorId = isset($row->contractor) ? (int) $row->contractor : null;
        $legacyRequestId = isset($row->request) ? (int) $row->request : null;

        $contractorId = $legacyContractorId
            ? Contractor::query()->where('legacy_id', $legacyContractorId)->value('id')
            : null;
        $requestId = $legacyRequestId ? $this->valuationRequestIdByLegacy($legacyRequestId) : null;

        $this->upsertByLegacyId(Contract::class, $legacyId, [
            'contractor_id' => $contractorId,
            'valuation_request_id' => $requestId,
            'legacy_contractor_id' => $legacyContractorId,
            'legacy_request_id' => $legacyRequestId,
            'state' => isset($row->state) ? (int) $row->state : null,
        ]);
    }
}
