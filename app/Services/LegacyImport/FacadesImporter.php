<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyFacade;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class FacadesImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'facades';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('facade');
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

        $this->upsertByLegacyId(PropertyFacade::class, $legacyId, [
            'property_id' => $propertyId,
            'facade_type_n' => $this->nullableString($row->facade_typen ?? null),
            'facade_type_s' => $this->nullableString($row->facade_types ?? null),
            'facade_type_e' => $this->nullableString($row->facade_typee ?? null),
            'facade_type_w' => $this->nullableString($row->facade_typew ?? null),
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
