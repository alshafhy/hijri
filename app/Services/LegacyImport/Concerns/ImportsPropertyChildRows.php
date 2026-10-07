<?php

declare(strict_types=1);

namespace App\Services\LegacyImport\Concerns;

use stdClass;

/**
 * Shared helpers for importers whose legacy rows hang off real_estate_information.
 */
trait ImportsPropertyChildRows
{
    use ResolvesImportedEntities;

    protected function resolvePropertyOrQuarantine(stdClass $row, int $legacyId): ?int
    {
        $propertyId = $this->propertyIdByLegacyRei((int) ($row->Real_Estate_Information ?? $row->real_estate_information_id ?? 0));
        if ($propertyId === null) {
            $this->quarantine(static::key(), $legacyId, (array) $row, 'property_not_imported');
        }

        return $propertyId;
    }

    protected function str(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }

    protected function int(mixed $value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
}
