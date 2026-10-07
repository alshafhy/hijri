<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\PropertyQuestion;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class QuestionsImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    public static function key(): string
    {
        return 'questions';
    }

    protected function legacyIdColumn(): string
    {
        return 'idquestion';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('question');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->idquestion;
        $propertyId = $this->resolvePropertyOrQuarantine($row, $legacyId);
        if ($propertyId === null) {
            return;
        }

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        PropertyQuestion::query()->updateOrCreate(
            ['property_id' => $propertyId],
            [
                'legacy_id' => $legacyId,
                'populated' => $this->str($row->populated ?? null),
                'identical' => $this->str($row->identical ?? null),
                'contrary' => $this->str($row->contrary ?? null),
                'concrete_roof' => $this->str($row->concrete_roof ?? null),
                'ready' => $this->str($row->ready ?? null),
                'ground_extension' => $this->str($row->ground_extension ?? null),
                'need_cost' => $this->str($row->need_cost ?? null),
                'cost' => $this->str($row->cost ?? null),
                'inside_inspection' => $this->str($row->inside_inspection ?? null),
                'outside_inspection' => $this->str($row->outside_inspection ?? null),
                'best_use' => $this->int($row->best_use ?? null) ?? 1,
            ]
        );
        $this->imported++;
    }
}
