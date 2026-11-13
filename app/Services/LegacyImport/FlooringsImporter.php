<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyFlooring;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class FlooringsImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    public static function key(): string
    {
        return 'floorings';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('flooring');
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

        PropertyFlooring::query()->updateOrCreate(
            ['property_id' => $propertyId],
            [
                'legacy_id' => $legacyId,
                'flooring_yards' => $this->str($row->flooring_typeco ?? null),
                'yards_description' => $this->str($row->co_description ?? null),
                'flooring_reception' => $this->str($row->flooring_typere ?? null),
                'reception_description' => $this->str($row->re_description ?? null),
                'flooring_entrance' => $this->str($row->flooring_typeen ?? null),
                'entrance_description' => $this->str($row->en_description ?? null),
                'flooring_rooms' => $this->str($row->flooring_typero ?? null),
                'rooms_description' => $this->str($row->ro_description ?? null),
            ]
        );
        $this->imported++;
    }
}
