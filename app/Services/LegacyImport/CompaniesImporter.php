<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Company;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class CompaniesImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'companies';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('companies');
    }

    protected function importRow(stdClass $row): void
    {
        $this->upsertByLegacyId(Company::class, (int) $row->id, [
            'name' => (string) ($row->name ?? ''),
            'name_en' => null,
            'status' => 1,
        ]);
    }
}
