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
    private const PART_SEPARATOR = "\u{00A0}|\u{00A0}";

    public function __construct(
        private readonly StandardsSection $standards = new StandardsSection,
        private readonly ReportLabels $labels = new ReportLabels,
    ) {}

    /**
     * @param  ?array{membership_number: ?string, reg_number: ?string, membership_date: ?string, accreditation_end_date: ?string}  $branding
     * @param  ?array{membership_branch_type: ?string, membership_category: ?string}  $evaluator
     * @return ?array{owner_name: ?string, rows: list<array{label: string, value: string}>, approaches: list<array{label: string, value: ?string}>, approach_note: ?string, approved: bool, company_lines: list<array{separator: string, parts: list<array{label: string, value: string}>}>}
     */
    public function build(ValuationRequest $request, ?Property $property, ?array $branding = null, ?array $evaluator = null): ?array
    {
        if ($property === null || $property->print_valuation_certificate !== true) {
            return null;
        }

        $approved = $request->state === 'approve';

        return [
            'owner_name' => Card::text($property->owner_name),
            'rows' => Card::make('', $this->rows($request, $property))['rows'] ?? [],
            'approaches' => $this->standards->approaches($property),
            'approach_note' => Card::text($property->note_valuation_way_type),
            'approved' => $approved,
            'company_lines' => $branding === null ? [] : $this->companyLines($branding, $evaluator, $approved),
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

    /**
     * Company block lines under the final value; parts with empty values and lines left empty are dropped.
     *
     * @param  array{membership_number: ?string, reg_number: ?string, membership_date: ?string, accreditation_end_date: ?string}  $branding
     * @param  ?array{membership_branch_type: ?string, membership_category: ?string}  $evaluator
     * @return list<array{separator: string, parts: list<array{label: string, value: string}>}>
     */
    private function companyLines(array $branding, ?array $evaluator, bool $approved): array
    {
        $lines = [
            [self::PART_SEPARATOR, [['membership_saudi_authority', $branding['membership_number']]]],
            [self::PART_SEPARATOR, [['company_membership_number', $branding['membership_number']], ['membership_date', $branding['membership_date']]]],
            [' - ', [['membership_branch', $evaluator['membership_branch_type'] ?? null], ['membership_category', $evaluator['membership_category'] ?? null]]],
            [self::PART_SEPARATOR, [['company_reg_number', $branding['reg_number']], ['end_date', $approved ? $branding['accreditation_end_date'] : null]]],
        ];

        $kept = [];
        foreach ($lines as [$separator, $parts]) {
            $parts = Card::make('', array_map(static fn (array $part): array => [Card::label($part[0]), $part[1]], $parts))['rows'] ?? [];
            if ($parts !== []) {
                $kept[] = ['separator' => $separator, 'parts' => $parts];
            }
        }

        return $kept;
    }
}
