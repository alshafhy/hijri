<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyBorder;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class BordersImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'borders';
    }

    protected function legacyIdColumn(): string
    {
        return 'idBorder';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('border');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->idBorder;
        $legacyReiId = (int) ($row->Real_estate_information ?? 0);
        $propertyId = $this->propertyIdByLegacyRei($legacyReiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'property_not_imported');

            return;
        }

        $this->upsertByLegacyId(PropertyBorder::class, $legacyId, [
            'property_id' => $propertyId,
            'north' => $this->nullableString($row->north ?? null),
            'north_length' => $this->nullableFloat($row->north_length ?? null),
            'east' => $this->nullableString($row->east ?? null),
            'east_length' => $this->nullableFloat($row->east_length ?? null),
            'south' => $this->nullableString($row->south ?? null),
            'south_length' => $this->nullableFloat($row->south_length ?? null),
            'west' => $this->nullableString($row->west ?? null),
            'west_length' => $this->nullableFloat($row->west_length ?? null),
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

    private function nullableFloat(mixed $value): ?float
    {
        return $value === null || $value === '' ? null : (float) $value;
    }
}
