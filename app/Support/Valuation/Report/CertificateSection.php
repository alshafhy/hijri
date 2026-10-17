<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Models\ValuationRequest;
use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy `firstPage-v2`: valuation certificate values, built only when the property asks for the certificate.
 */
final class CertificateSection
{
    public function __construct(
        private readonly StandardsSection $standards = new StandardsSection,
        private readonly ReportLabels $labels = new ReportLabels,
    ) {}

    /**
     * @return ?array{owner_name: ?string, rows: list<array{label: string, value: string}>, approaches: list<array{label: string, value: ?string}>, approach_note: ?string, approved: bool}
     */
    public function build(ValuationRequest $request, ?Property $property): ?array
    {
        if ($property === null || $property->print_valuation_certificate !== true) {
            return null;
        }

        return [
            'owner_name' => Card::text($property->owner_name),
            'rows' => Card::make('', $this->rows($request, $property))['rows'] ?? [],
            'approaches' => $this->standards->approaches($property),
            'approach_note' => Card::text($property->note_valuation_way_type),
            'approved' => $request->state === 'approve',
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function rows(ValuationRequest $request, Property $property): array
    {
        $date = $this->standards->evaluationDate($property) ?? $request->evaluated_at;

        return [
            [Card::label('purpose_of_evaluation'), $this->standards->purpose($property)],
            [Card::label('base_value'), $this->labels->option('base_value', $property->base_value)],
            [Card::label('evaluation_date'), $date?->format('Y/m/d')],
            [Card::label('note_base_value'), Card::text($property->note_base_value)],
            [Card::label('value_assumption'), $this->labels->option('value_assumption', $property->value_assumption)],
            [Card::label('property_inspection'), Card::label(
                trim((string) $property->question?->inside_inspection) === 'نعم' ? 'inspection_inside_outside' : 'inspection_outside_only'
            )],
        ];
    }
}
