<?php

declare(strict_types=1);

use App\Support\HijriDate;
use App\Support\Valuation\Report\StandardsSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->setLocale('ar'));

/**
 * @param  list<array{title: string, rows: list<array{label: string, value: string}>}>  $cards
 * @return array<string, string>
 */
function standardsRows(array $cards): array
{
    $rows = [];
    foreach ($cards as $card) {
        foreach ($card['rows'] as $row) {
            $rows[$row['label']] = $row['value'];
        }
    }

    return $rows;
}

it('resolves coded standards to their labels', function () {
    $cards = (new StandardsSection)->build(reportFixture()->property);
    $rows = standardsRows($cards);

    expect(array_column($cards, 'title'))->toBe([__('pdf.work_scope'), __('pdf.assessment_method_information')])
        ->and($rows[__('pdf.purpose_of_evaluation')])->toBe('تقييم الأصول')
        ->and($rows[__('pdf.intended_use')])->toBe('تقييم الأصول')
        ->and($rows[__('pdf.base_value')])->toBe('القيمة السوقية')
        ->and($rows[__('pdf.value_assumption')])->toBe('البيع القسري')
        ->and($rows[__('pdf.market_valuation_way_main')])->toBe('أساسى لتقدير القيمة')
        ->and($rows[__('pdf.informations_source')])->toBe('منصة السوق')
        ->and($rows[__('pdf.evaluation_date')])->toBe('2026/10/01')
        ->and($rows[__('pdf.evaluation_date_hijri')])->toBe('1448/04/09')
        ->and($rows[__('pdf.deposit_number')])->toBe('DEP-1');
});

it('appends the sub approach and prefers the free-text purpose', function () {
    $property = reportFixture()->property;
    $property->update(['market_valuation_way_sub' => 1, 'valuation_type_desc' => 'غرض خاص']);

    $rows = standardsRows((new StandardsSection)->build($property->refresh()));

    expect($rows[__('pdf.market_valuation_way_main')])->toBe('أساسى لتقدير القيمة - طريقة المعاملات المقارنة')
        ->and($rows[__('pdf.purpose_of_evaluation')])->toBe('غرض خاص');
});

it('drops empty rows and applies the legacy fallbacks', function () {
    $rows = standardsRows((new StandardsSection)->build(reportFixture()->property));

    expect($rows)->not->toHaveKey(__('pdf.income_valuation_way_main'))
        ->and($rows)->not->toHaveKey(__('pdf.conclusion_value'))
        ->and($rows)->not->toHaveKey(__('pdf.note_base_value'))
        ->and($rows[__('pdf.search_scope')])->toBe(__('pdf.search_scope_des'))
        ->and($rows[__('pdf.other_users')])->toBe(__('pdf.none'));

    foreach ($rows as $value) {
        expect(trim($value))->not->toBe('');
    }
});

it('computes the hijri date when none is stored', function () {
    $property = reportFixture()->property;
    $property->update(['evaluation_date_hijri' => null]);

    $rows = standardsRows((new StandardsSection)->build($property->refresh()));

    expect($rows[__('pdf.evaluation_date_hijri')])->toBe(HijriDate::fromGregorian('2026-10-01')->numeric());
});
