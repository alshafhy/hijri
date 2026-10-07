<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Partner;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class PartnersImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'partners';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('partner');
    }

    protected function importRow(stdClass $row): void
    {
        $this->upsertByLegacyId(Partner::class, (int) $row->id, [
            'legacy_user_id' => isset($row->user_id) ? (int) $row->user_id : null,
            'name' => (string) ($row->name ?? ''),
            'email' => $this->nullableString($row->email ?? null),
            'phone_number' => $this->nullableString($row->phone_number ?? null),
            'x_axis' => $this->nullableString($row->x_axis ?? null),
            'y_axis' => $this->nullableString($row->y_axis ?? null),
            'state' => isset($row->state) ? (int) $row->state : null,
            'created_by_legacy_user_id' => isset($row->created_user_id) ? (int) $row->created_user_id : null,
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
