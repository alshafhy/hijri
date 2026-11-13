<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Support\HijriDate;
use App\Support\Valuation\Report\Cards\Card;
use DateTimeInterface;

/**
 * Valuation standards cards (scope of work and valuation approaches) for valuation reports.
 */
final class StandardsSection
{
    private const APPROACHES = [
        'market' => 'market_valuation_way_main',
        'income' => 'income_valuation_way_main',
        'cost' => 'cost_valuation',
    ];

    public function __construct(private readonly ReportLabels $labels = new ReportLabels) {}

    /**
     * @return list<array{title: string, rows: list<array{label: string, value: string}>}>
     */
    public function build(Property $property): array
    {
        return array_values(array_filter([
            Card::make(Card::label('work_scope'), [...$this->purposeRows($property), ...$this->scopeRows($property)]),
            Card::make(Card::label('assessment_method_information'), $this->approachRows($property)),
        ]));
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function purposeRows(Property $property): array
    {
        return [
            [Card::label('purpose_of_evaluation'), $this->purpose($property)],
            [Card::label('intended_use'), $this->intendedUse($property)],
            [Card::label('base_value'), $this->labels->option('base_value', $property->base_value)],
            [Card::label('note_base_value'), Card::text($property->note_base_value)],
            [Card::label('value_assumption'), $this->labels->option('value_assumption', $property->value_assumption)],
            [Card::label('method_of_extracting_value'), Card::text($property->type_of_getings_value)],
            [Card::label('submitted_documents'), Card::text($property->requested_papers)],
            [Card::label('assumptions'), Card::text($property->assumptions)],
            [Card::label('important_assumptions'), Card::text($property->important_assumptions)],
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function scopeRows(Property $property): array
    {
        return [
            [Card::label('search_scope'), Card::text($property->area_of_search) ?? Card::label('search_scope_des')],
            [Card::label('area_approval_way'), Card::text($property->area_approval_way)],
            [Card::label('informations_source'), Card::text($property->informations_source)],
            [Card::label('conclusion_value'), Card::text($property->conclusion_value)],
            [Card::label('other_users'), Card::text($property->getAttribute('other_users')) ?? Card::label('none')],
            [Card::label('evaluation_date'), $this->evaluationDate($property)?->format('Y/m/d')],
            [Card::label('evaluation_date_hijri'), $this->hijriDate($property)],
            [Card::label('deposit_number'), Card::text($property->deposit_number)],
        ];
    }

    public function purpose(Property $property): ?string
    {
        return Card::text($property->valuation_type_desc)
            ?? $this->labels->option('valuation_type', $property->valuation_type);
    }

    public function intendedUse(Property $property): ?string
    {
        return Card::text($property->valuation_usage_desc)
            ?? $this->labels->option('valuation_usage', $property->valuation_usage);
    }

    /**
     * Market / income / cost approach rows (`main - sub`), always all three as in the legacy certificate.
     *
     * @return list<array{label: string, value: ?string}>
     */
    public function approaches(Property $property): array
    {
        $rows = [];
        foreach (self::APPROACHES as $approach => $labelKey) {
            $main = $property->{"{$approach}_valuation_way_main"};
            $sub = $main ? $this->labels->option("{$approach}_valuation_way_sub", $property->{"{$approach}_valuation_way_sub"}) : null;
            $rows[] = ['label' => Card::label($labelKey), 'value' => Card::join([$this->labels->option("{$approach}_valuation_way_main", $main), $sub], ' - ')];
        }

        return $rows;
    }

    public function evaluationDate(Property $property): ?DateTimeInterface
    {
        $date = $property->getAttribute('evaluation_date');

        return $date instanceof DateTimeInterface ? $date : null;
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function approachRows(Property $property): array
    {
        $rows = array_map(
            static fn (array $row): array => [$row['label'], $row['value']],
            $this->approaches($property),
        );
        $rows[] = [Card::label('method_of_extracting_value_label'), Card::text($property->note_valuation_way_type)];

        return $rows;
    }

    private function hijriDate(Property $property): ?string
    {
        $stored = Card::text($property->evaluation_date_hijri);
        $date = $this->evaluationDate($property);
        if ($stored !== null || $date === null) {
            return $stored;
        }

        return HijriDate::fromGregorian($date)->numeric();
    }
}
