<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyLand;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class LandsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'lands';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('land');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $legacyReiId = (int) ($row->Real_Estate_Information ?? 0);
        $propertyId = $this->propertyIdByLegacyRei($legacyReiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'property_not_imported');

            return;
        }

        $this->upsertByLegacyId(PropertyLand::class, $legacyId, [
            'property_id' => $propertyId,
            'land_nature' => $this->nullableString($row->land_nature ?? null),
            'facade' => $this->nullableString($row->facade ?? null),
            'site' => $this->nullableString($row->site ?? null),
            'door_ex' => $this->nullableString($row->door_ex ?? null),
            'street' => $this->nullableString($row->street ?? null),
            'door_in' => $this->nullableString($row->door_in ?? null),
            'neighbor' => $this->nullableString($row->neighbor ?? null),
            'include' => $this->nullableString($row->include ?? null),
            'heater_no' => $this->nullableString($row->heater_no ?? null),
            'lift_no' => $this->nullableString($row->lift_no ?? null),
            'bathroom_a_no' => $this->nullableString($row->bathroonA_no ?? null),
            'bathroom_e_no' => $this->nullableString($row->bathroonE_no ?? null),
            'electricity_no' => $this->nullableString($row->electricity_no ?? null),
            'electricity_num' => $this->nullableString($row->electricity_num ?? null),
            'water_no' => $this->nullableString($row->water_no ?? null),
            'water_num' => $this->nullableString($row->water_num ?? null),
            'ownership_type' => $this->nullableInt($row->ownership_type ?? null),
            'furnishing_status' => $this->nullableInt($row->furnishing_status ?? null),
            'building_type' => $this->nullableInt($row->building_type ?? null),
            'finishing_status' => $this->nullableInt($row->finishing_status ?? null),
            'street_width' => $this->nullableFloat($row->street_width ?? null),
            'total_building_area' => $this->nullableFloat($row->total_building_area ?? null),
            'additional_features' => $this->nullableString($row->additional_features ?? null),
            'approved_floors' => $this->nullableFloat($row->approved_floors ?? null),
            'property_use' => $this->nullableString($row->property_use ?? null),
            'approved_building' => $this->nullableFloat($row->approved_building ?? null),
            'sector_description' => $this->nullableString($row->sector_description ?? null),
            'ownership_description' => $this->nullableString($row->ownership_description ?? null),
            'market_value' => $this->nullableInt($row->market_value ?? null),
            'market_value_description' => $this->nullableString($row->marketValue_description ?? null),
            'surrounding_facilities' => $this->nullableString($row->surrounding_facilities ?? null),
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
