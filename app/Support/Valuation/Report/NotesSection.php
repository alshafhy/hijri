<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;

/**
 * Legacy "opinion of the appraisal company" table: coordinator notes and evaluator notes, printed even when empty.
 */
final class NotesSection
{
    /**
     * @return array{coordinator: string, evaluator: string}|null
     */
    public function build(?Property $property): ?array
    {
        if ($property === null) {
            return null;
        }

        return [
            'coordinator' => trim((string) $property->notes_real_estate),
            'evaluator' => trim((string) $property->notes_property),
        ];
    }
}
