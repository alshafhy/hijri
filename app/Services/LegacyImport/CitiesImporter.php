<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\GeoCity;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class CitiesImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'cities';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('city');
    }

    protected function importRow(stdClass $row): void
    {
        $this->upsertByLegacyId(GeoCity::class, (int) $row->id, [
            'name_ar' => (string) ($row->city ?? ''),
            'name_en' => null,
        ]);
    }
}
