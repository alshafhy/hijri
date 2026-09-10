<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Contractor;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class ContractorsImporter extends ResumableImporter
{
    public static function key(): string
    {
        return 'contractors';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('contractor');
    }

    protected function importRow(stdClass $row): void
    {
        $this->upsertByLegacyId(Contractor::class, (int) $row->id, [
            'legacy_user_id' => isset($row->user_id) ? (int) $row->user_id : null,
            'name' => (string) ($row->name ?? ''),
            'email' => $this->nullableString($row->email ?? null),
            'phone_number' => $this->nullableString($row->phone_number ?? null),
            'x_axis' => isset($row->x_axis) ? (float) $row->x_axis : null,
            'y_axis' => isset($row->y_axis) ? (float) $row->y_axis : null,
            'date_from' => $row->date_from ?? null,
            'date_to' => $row->date_to ?? null,
            'fees' => isset($row->fees) ? (int) $row->fees : null,
            'state' => isset($row->state) ? (int) $row->state : null,
            'template_id' => isset($row->template_id) ? (int) $row->template_id : null,
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
