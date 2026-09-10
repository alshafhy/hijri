<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\RequestFeeShare;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class FeeSharesImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'fee_shares';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('request_shares');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $requestId = $this->valuationRequestIdByLegacy((int) ($row->request_id ?? 0));
        if ($requestId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'request_not_imported');

            return;
        }

        $this->upsertByLegacyId(RequestFeeShare::class, $legacyId, [
            'valuation_request_id' => $requestId,
            'coordinator_share' => isset($row->coordinator) ? (int) $row->coordinator : null,
            'evaluator_share' => isset($row->evaluator) ? (int) $row->evaluator : null,
            'manager_share' => isset($row->manager) ? (int) $row->manager : null,
        ]);
    }
}
