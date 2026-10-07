<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyComparable;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class ComparablesImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'comparables';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('real_estate_comparisons');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyReiId = (int) ($row->real_estate_information_id ?? 0);
        $propertyId = $this->propertyIdByLegacyRei($legacyReiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), (int) $row->id, (array) $row, 'property_not_imported');

            return;
        }

        $infoSource = $this->nullableString($row->information_source ?? null);

        for ($seq = 1; $seq <= 4; $seq++) {
            $type = $row->{"real_estate_type_{$seq}"} ?? null;
            $streetWidth = $row->{"street_width_{$seq}"} ?? null;
            $frontNumber = $row->{"front_number_{$seq}"} ?? null;
            $frontType = $row->{"front_type_{$seq}"} ?? null;
            $area = $row->{"area_{$seq}"} ?? null;
            $price = $row->{"price_{$seq}"} ?? null;
            $percentage = $row->{"percentage_{$seq}"} ?? null;
            $coords = $row->{"coordinates_url_{$seq}"} ?? null;

            if ($this->allEmpty([$type, $streetWidth, $frontNumber, $frontType, $area, $price, $percentage, $coords])) {
                continue;
            }

            if ($this->dryRun) {
                $this->imported++;

                continue;
            }

            PropertyComparable::query()->updateOrCreate(
                ['property_id' => $propertyId, 'sequence' => $seq],
                [
                    'real_estate_type' => $this->nullableString($type),
                    'street_width' => $streetWidth !== null && $streetWidth !== '' ? (int) $streetWidth : null,
                    'front_number' => $frontNumber !== null && $frontNumber !== '' ? (int) $frontNumber : null,
                    'front_type' => $this->nullableString($frontType),
                    'area' => $area !== null && $area !== '' ? (float) $area : null,
                    'price' => $price !== null && $price !== '' ? (float) $price : null,
                    'percentage' => $percentage !== null && $percentage !== '' ? (int) $percentage : null,
                    'coordinates_url' => $this->nullableString($coords),
                    'information_source' => $infoSource,
                ]
            );
            $this->imported++;
        }
    }

    /** @param list<mixed> $values */
    private function allEmpty(array $values): bool
    {
        foreach ($values as $v) {
            if ($v !== null && $v !== '') {
                return false;
            }
        }

        return true;
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
