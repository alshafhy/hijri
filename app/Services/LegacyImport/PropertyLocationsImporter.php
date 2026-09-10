<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Property;
use App\Models\PropertyLocation;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class PropertyLocationsImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'property_locations';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('location');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyLocationId = (int) $row->id;

        $property = Property::query()->where('legacy_location_id', $legacyLocationId)->first();
        if ($property === null) {
            $this->skipped++;

            return;
        }

        $cityLegacy = isset($row->City) ? (int) $row->City : 0;
        $neighLegacy = isset($row->Neighborhood) ? (int) $row->Neighborhood : 0;

        $attrs = [
            'property_id' => $property->id,
            'legacy_location_id' => $legacyLocationId,
            'city_id' => $cityLegacy > 0 ? GeoCity::query()->where('legacy_id', $cityLegacy)->value('id') : null,
            'neighborhood_id' => $neighLegacy > 0 ? GeoNeighborhood::query()->where('legacy_id', $neighLegacy)->value('id') : null,
            'sketch_name' => $this->nullableString($row->sketch_name ?? null),
            'street' => $this->nullableString($row->street ?? null),
            'sketch_no' => $this->nullableString($row->sketch_No ?? null),
            'part_no' => $this->nullableString($row->part_No ?? null),
            'piece_number' => $this->nullableString($row->piece_number ?? null),
            'x_axis' => $this->nullableString($row->x_axis ?? null),
            'y_axis' => $this->nullableString($row->y_axis ?? null),
        ];

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        PropertyLocation::query()->updateOrCreate(
            ['property_id' => $property->id],
            $attrs
        );
        $this->imported++;
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
