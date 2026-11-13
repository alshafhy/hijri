<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Models\ValuationRequest;
use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy `executiveSummary`: rows of the two executive summary cards (the first also holds the preparation team).
 */
final class SummarySection
{
    public function __construct(private readonly StandardsSection $standards = new StandardsSection) {}

    /**
     * @param  array{evaluator: ?array{name: string, membership_number: ?string}, approver: ?array{membership_number: ?string}}  $signatures
     * @return ?array{intro: list<array{label: string, value: string}>, team: array{evaluator_name: string, evaluator_membership: string, company_name: string, approver_membership: string}, rows: list<array{label: string, value: string}>, continued: list<array{label: string, value: string}>}
     */
    public function build(ValuationRequest $request, ?Property $property, string $companyName, array $signatures, string $locale = 'ar'): ?array
    {
        if ($property === null) {
            return null;
        }

        return [
            'intro' => self::rows([
                ['subject_to_the_report', Card::label('subject_to_the_report_des')],
                ['resident_status', Card::label('resident_status_des')],
            ]),
            'team' => [
                'evaluator_name' => $signatures['evaluator']['name'] ?? '',
                'evaluator_membership' => $signatures['evaluator']['membership_number'] ?? '',
                'company_name' => $companyName,
                'approver_membership' => $signatures['approver']['membership_number'] ?? '',
            ],
            'rows' => self::rows($this->firstCardRows($request, $property)),
            'continued' => self::rows($this->secondCardRows($request, $property, $companyName, $locale)),
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function firstCardRows(ValuationRequest $request, Property $property): array
    {
        return [
            ['inspection_date', $request->started_at?->format('Y/m/d')],
            ['evaluation_date', ($this->standards->evaluationDate($property) ?? $request->evaluated_at)?->format('Y/m/d')],
            ['report_issue_date', now()->format('Y/m/d')],
            ['valuation_currency', Card::label('saudi_riyal')],
            ['purpose_of_evaluation', $this->standards->purpose($property)],
            ['intended_use', $this->standards->intendedUse($property) ?? Card::label('none')],
            ['value_definition', Card::text($property->note_base_value) ?? Card::label('rental_value_definition_des')],
            ['method_of_extracting_value', Card::text($property->type_of_getings_value)],
            ['submitted_documents', Card::text($property->requested_papers)],
        ];
    }

    /**
     * The english report shows the english customer and owner names, falling back to the
     * linked partner/contractor name and then the arabic names.
     *
     * @return list<array{0: string, 1: ?string}>
     */
    private function secondCardRows(ValuationRequest $request, Property $property, string $companyName, string $locale): array
    {
        $customer = Card::text($property->customer_name);
        $reportUser = $customer;

        if ($locale === 'en') {
            $client = self::clientName($request);
            $customer = self::firstNonBlank([
                $property->customer_name_en,
                $client,
                $property->customer_name,
            ]);
            $reportUser = self::firstNonBlank([
                $property->owner_name_en,
                $client,
                $property->owner_name,
                $property->customer_name,
            ]);
        }

        return [
            ['other_users', Card::text($property->getAttribute('other_users')) ?? Card::label('none')],
            ['assumptions', Card::text($property->assumptions)],
            ['search_scope', Card::text($property->area_of_search) ?? Card::label('search_scope_des')],
            ['area_approval_way', Card::text($property->area_approval_way)],
            ['evaluation_period', Card::label('evaluation_period_des')],
            ['resident_identity', $companyName.' :- '.Card::label('resident_identity_des')],
            ['customer_name', $customer],
            ['report_user_name', $reportUser],
            ['location', Card::label('location_des')],
            ['inspection', Card::label('inspection_des')],
            ['compliance_statement', Card::label('compliance_statement_des')],
        ];
    }

    /**
     * @param  list<mixed>  $names
     */
    private static function firstNonBlank(array $names): ?string
    {
        foreach ($names as $name) {
            if (is_scalar($name) && trim((string) $name) !== '') {
                return Card::text($name);
            }
        }

        return null;
    }

    private static function clientName(ValuationRequest $request): ?string
    {
        $clients = [
            $request->partnerContracts->first()?->partner,
            $request->contracts->first()?->contractor,
        ];

        foreach ($clients as $client) {
            if ($client === null) {
                continue;
            }

            $name = self::firstNonBlank([
                $client->getAttribute('name_en'),
                $client->getAttribute('name'),
            ]);

            if ($name !== null) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Legacy prints every summary row, empty values included.
     *
     * @param  list<array{0: string, 1: ?string}>  $rows
     * @return list<array{label: string, value: string}>
     */
    private static function rows(array $rows): array
    {
        return array_map(
            static fn (array $row): array => ['label' => Card::label($row[0]), 'value' => $row[1] ?? ''],
            $rows,
        );
    }
}
