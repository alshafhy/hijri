<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyAdjustment;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class AdjustmentsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'adjustments';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('real_estate_adjustments');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $legacyReiId = (int) ($row->real_estate_information_id ?? 0);
        $propertyId = $this->propertyIdByLegacyRei($legacyReiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'property_not_imported');

            return;
        }

        $transactionDate = $this->attrTatweel($row, 'transaction_date');

        $this->upsertByLegacyId(PropertyAdjustment::class, $legacyId, [
            'property_id' => $propertyId,
            'type' => isset($row->type) ? (int) $row->type : null,
            'transaction_date' => $transactionDate,
            'market_conditions_value' => $this->nullableString($row->market_conditions_value ?? null),
            'market_conditions_percentage' => $this->nullableString($row->market_conditions_percentage ?? null),
            'financing_terms_value' => $this->nullableString($row->financing_terms_value ?? null),
            'financing_terms_percentage' => $this->nullableString($row->financing_terms_percentage ?? null),
            'sale_terms_value' => $this->nullableString($row->sale_terms_value ?? null),
            'sale_terms_percentage' => $this->nullableString($row->sale_terms_percentage ?? null),
            'area_percentage' => $this->nullableInt($row->area_percentage ?? null),
            'location_status_value' => $this->nullableInt($row->location_status_value ?? null),
            'location_status_percentage' => $this->nullableInt($row->location_status_percentage ?? null),
            'easy_access_value' => $this->nullableInt($row->easy_access_value ?? null),
            'easy_access_percentage' => $this->nullableInt($row->easy_access_percentage ?? null),
            'main_street_width_value' => $this->nullableInt($row->main_street_width_value ?? null),
            'main_street_width_percentage' => $this->nullableInt($row->main_street_width_percentage ?? null),
            'streets_count_value' => $this->nullableInt($row->streets_count_value ?? null),
            'streets_count_percentage' => $this->nullableInt($row->streets_count_percentage ?? null),
            'land_shape_value' => $this->nullableInt($row->land_shape_value ?? null),
            'land_shape_percentage' => $this->nullableInt($row->land_shape_percentage ?? null),
            'land_topography_value' => $this->nullableString($row->land_topography_value ?? null),
            'land_topography_percentage' => $this->nullableInt($row->land_topography_percentage ?? null),
            'natural_factors_value' => $this->nullableString($row->natural_factors_value ?? null),
            'natural_factors_percentage' => $this->nullableInt($row->natural_factors_percentage ?? null),
            'near_main_street_value' => $this->nullableInt($row->near_main_street_value ?? null),
            'near_main_street_percentage' => $this->nullableInt($row->near_main_street_percentage ?? null),
            'other_value' => $this->nullableString($row->other_value ?? null),
            'other_percentage' => $this->nullableInt($row->other_percentage ?? null),
            'total_adjustments_value' => $this->nullableFloat($row->total_adjustments_value ?? null),
            'total_adjustments_price' => $this->nullableFloat($row->total_adjustments_price ?? null),
            'meter_price' => $this->nullableFloat($row->meter_price ?? null),
            'meter_price_approximately' => $this->nullableFloat($row->meter_price_approximately ?? null),
            'proportional_adjustment' => $this->nullableInt($row->proportional_adjustment ?? null),
            'settlement_ratio' => $this->nullableFloat($row->settlement_ratio ?? null),
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

    private function nullableInt(mixed $value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
