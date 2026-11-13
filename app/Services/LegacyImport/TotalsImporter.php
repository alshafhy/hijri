<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyTotal;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class TotalsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'property_totals';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('building_total_values');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $reiId = (int) ($row->real_estate_information_id ?? 0);

        if ($reiId <= 0) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'missing_rei_id');

            return;
        }

        $propertyId = $this->propertyIdByLegacyRei($reiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'property_not_imported');

            return;
        }

        $this->upsertByLegacyId(PropertyTotal::class, $legacyId, [
            'property_id' => $propertyId,
            'profit_percentage' => $this->nullableFloat($row->profit_percentage ?? null),
            'profit_amount' => $this->nullableFloat($row->profit_amount ?? null),
            'depreciation_type' => isset($row->depreciation_type) ? (int) $row->depreciation_type : null,
            'calc_amount' => $this->nullableFloat($row->calc_amount ?? null),
            'depreciation_amount' => $this->nullableFloat($row->depreciation_amount ?? null),
            'forced_sale_percentage' => isset($row->forced_sale_percentage) ? (int) $row->forced_sale_percentage : null,
            'forced_sale_amount' => $this->nullableFloat($row->forced_sale_amount ?? null),
            'total_amount' => $this->nullableFloat($row->total_amount ?? null),
            'total_amount_manual' => $this->nullableFloat($row->total_amount_manual ?? null),
            'hide_comparisons_info_table' => (bool) ($row->hide_comparisons_info_table ?? false),
            'hide_evaluation_info_table' => (bool) ($row->hide_evaluation_info_table ?? false),
            'total_area' => $this->nullableFloat($row->total_area ?? null),
            'base_area' => $this->nullableFloat($row->base_area ?? null),
            'base_amount' => $this->nullableFloat($row->base_amount ?? null),
            'movables' => $this->nullableFloat($row->movables ?? null),
        ]);
    }

    private function nullableFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }
}
