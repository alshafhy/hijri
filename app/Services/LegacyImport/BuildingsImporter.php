<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyBuilding;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

/**
 * Legacy `residential` rows → property_buildings (kind=residential).
 */
class BuildingsImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    public static function key(): string
    {
        return 'buildings';
    }

    protected function kind(): string
    {
        return 'residential';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table($this->kind());
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $propertyId = $this->resolvePropertyOrQuarantine($row, $legacyId);
        if ($propertyId === null) {
            return;
        }

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        PropertyBuilding::query()->updateOrCreate(
            ['property_id' => $propertyId],
            [
                'legacy_key' => $this->kind().':'.$legacyId,
                'kind' => $this->kind(),
                'floor_no' => $this->int($row->floor_No ?? null),
                'build_age' => $this->str($row->build_Age ?? null),
                'build_state' => $this->str($row->build_state ?? null),
                'architectural' => $this->str($row->architectural ?? null),
                'quality_insulation' => $this->str($row->quality_insulation ?? null),
                'bishop_type' => $this->str($row->bishop_type ?? null),
                'structure_construction' => $this->str($row->structure_construction ?? null),
                'default_build_age' => $this->int($row->default_build_age ?? null),
                'remaining_build_age' => $this->int($row->remaining_build_age ?? null),
                'house_unit_no' => $this->str($row->house_unit_No ?? null),
                'commercial_unit_no' => $this->str($row->commercial_unit_No ?? null),
                'description' => $this->str($row->description ?? null),
            ]
        );
        $this->imported++;
    }
}
