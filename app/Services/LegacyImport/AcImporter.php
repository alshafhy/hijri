<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyAc;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class AcImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    /** Legacy yes/no column => edit-form AC type label. */
    private const TYPES = [
        'central' => 'مركزي',
        'split' => 'سبيلت',
        'window' => 'شباك',
        'ncomposite' => 'غير مركب',
        'composite' => 'مركب',
        'connections' => 'توجد التوصيلات فقط',
    ];

    public static function key(): string
    {
        return 'acs';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('ac');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $propertyId = $this->resolvePropertyOrQuarantine($row, $legacyId);
        if ($propertyId === null) {
            return;
        }

        $types = [];
        foreach (self::TYPES as $column => $label) {
            if (trim((string) ($row->{$column} ?? '')) === 'نعم') {
                $types[] = $label;
            }
        }

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        PropertyAc::query()->updateOrCreate(
            ['property_id' => $propertyId],
            [
                'legacy_id' => $legacyId,
                'types' => $types === [] ? null : implode(',', $types),
                'ac_count' => $this->int($row->composite_no ?? null),
            ]
        );
        $this->imported++;
    }
}
