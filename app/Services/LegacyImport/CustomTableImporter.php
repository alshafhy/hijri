<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\ValuationCustomTableRow;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class CustomTableImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    public static function key(): string
    {
        return 'custom_table';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('custom_table');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $requestId = $this->valuationRequestIdByLegacy((int) ($row->request_id ?? 0));
        if ($requestId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'request_not_imported');

            return;
        }

        $raw = $row->value ?? null;
        $decoded = is_string($raw) ? json_decode($raw, true) : null;
        $values = is_array($decoded) ? array_values(array_map('strval', $decoded)) : [(string) ($raw ?? '')];

        $this->upsertByLegacyId(ValuationCustomTableRow::class, $legacyId, [
            'valuation_request_id' => $requestId,
            'table_title' => $this->str($row->table_title ?? null),
            'label' => (string) ($row->label ?? ''),
            'values' => $values,
            'sort_order' => (int) ($row->sort_order ?? 0),
        ]);
    }
}
