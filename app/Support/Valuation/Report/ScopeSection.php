<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy `conditionsTable`: scope of work rows in legacy order; the deposit number row is printed from `cover`.
 */
final class ScopeSection
{
    public function __construct(private readonly ReportLabels $labels = new ReportLabels) {}

    /**
     * @param  array{property_kind: ?string, property_type: ?string}  $cover
     * @return ?list<array{label: string, value: string}>
     */
    public function build(?Property $property, array $cover): ?array
    {
        if ($property === null) {
            return null;
        }

        $baseValue = $this->labels->option('base_value', $property->base_value);

        return Card::make('', [
            [Card::label('restrictions_on_use'), Card::label('restrictions_on_use_des')],
            [Card::label('main_input'), Card::label('data_available_in_instrument')],
            [Card::label('report_type_label'), Card::label('التقرير الكامل')],
            [Card::label('base_value_used'), $baseValue === null ? null : "({$baseValue})"],
            [Card::label('value_base_description'), Card::label('liquidation_value_des')],
            [Card::label('informations_source'), Card::text($property->informations_source)],
            [Card::label('important_assumptions'), Card::text($property->important_assumptions)],
            [Card::label('conclusion_value'), Card::text($property->conclusion_value)],
            [Card::label('property_type_label'), Card::join([$cover['property_kind'], $cover['property_type']], ' - ')],
            [Card::label('specialist'), Card::label('internal_evaluation')],
            [Card::label('env_gov_factors'), Card::label('seg_considered')],
            [Card::label('resident_independence'), Card::label('resident_independence_details')],
            [Card::label('value_assumption'), $this->labels->option('value_assumption', $property->value_assumption)],
            [Card::label('disclaimer'), Card::label('disclaimer_des')],
            [Card::label('use_nature'), $cover['property_kind']],
        ])['rows'] ?? [];
    }
}
