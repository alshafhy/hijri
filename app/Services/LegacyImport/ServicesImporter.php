<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyService;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class ServicesImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'services';
    }

    protected function legacyIdColumn(): string
    {
        return 'idService';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('service');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->idService;
        $legacyReiId = (int) ($row->Real_Estate_Information ?? 0);
        $propertyId = $this->propertyIdByLegacyRei($legacyReiId);
        if ($propertyId === null) {
            $this->quarantine(self::key(), $legacyId, (array) $row, 'property_not_imported');

            return;
        }

        $this->upsertByLegacyId(PropertyService::class, $legacyId, [
            'property_id' => $propertyId,
            'electricity' => $this->nullableString($row->electricity ?? null),
            'water' => $this->nullableString($row->water ?? null),
            'sanitation' => $this->nullableString($row->sanitation ?? null),
            'telephone' => $this->nullableString($row->telephone ?? null),
            'internet' => $this->nullableString($row->internet ?? null),
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
}
