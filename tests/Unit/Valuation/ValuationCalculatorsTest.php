<?php

declare(strict_types=1);

use App\Support\Valuation\ComparablesCalculator;
use App\Support\Valuation\CostApproachCalculator;
use App\Support\Valuation\ValuationFieldGrid;
use Tests\TestCase;

uses(TestCase::class);

it('computes the cost approach with profit and straight-line depreciation', function () {
    $result = (new CostApproachCalculator)->calculate([
        'land_area' => ['area' => 500, 'price' => 1000],
        'floor_g' => ['area' => 300, 'price' => 1500],
        'floor_a' => ['area' => 300, 'price' => 1500],
    ], [
        'profit_percentage' => 10,
        'depreciation_type' => 1,
        'build_age' => 10,
        'default_build_age' => 50,
        'movables' => 5000,
    ]);

    expect($result['total'])->toBe(1_400_000.0)
        ->and($result['land'])->toBe(500_000.0)
        ->and($result['total_area'])->toBe(1100.0)
        ->and($result['profit_amount'])->toBe(90_000.0)
        ->and($result['depreciation_amount'])->toBe(198_000.0)
        ->and($result['total_amount'])->toBe(1_297_000.0)
        ->and($result['forced_sale_amount'])->toBe(0.0);
});

it('uses extended-life depreciation and forced-sale discount', function () {
    $result = (new CostApproachCalculator)->calculate([
        'share_land' => ['area' => 100, 'price' => 1000],
        'floor_g' => ['area' => 100, 'price' => 1000],
    ], [
        'depreciation_type' => 2,
        'default_build_age' => 40,
        'remaining_build_age' => 30,
        'value_assumption' => CostApproachCalculator::FORCED_SALE_ASSUMPTION,
        'forced_sale_percentage' => 20,
    ]);

    expect($result['land'])->toBe(100_000.0)
        ->and($result['depreciation_amount'])->toBe(25_000.0)
        ->and($result['forced_sale_amount'])->toBe(40_000.0)
        ->and($result['total_amount'])->toBe(135_000.0);
});

it('skips depreciation when the default build age is missing or age is not numeric', function () {
    $calculator = new CostApproachCalculator;
    $rows = ['floor_g' => ['area' => 10, 'price' => 100]];

    expect($calculator->calculate($rows, ['depreciation_type' => 1, 'build_age' => 5])['depreciation_amount'])->toBe(0.0)
        ->and($calculator->calculate($rows, ['depreciation_type' => 1, 'build_age' => 'جديد', 'default_build_age' => 40])['depreciation_amount'])->toBe(0.0);
});

it('applies two-stage comparable adjustments and weights the meter price', function () {
    $result = (new ComparablesCalculator)->calculate([
        1 => ['area' => 400, 'price' => 1000, 'market_conditions_percentage' => 10, 'location_status_percentage' => -5, 'proportional_adjustment' => 60],
        2 => ['area' => 500, 'price' => 2000, 'settlement_ratio' => -10, 'area_percentage' => 10, 'proportional_adjustment' => 40],
    ]);

    expect($result['rows'][1]['total'])->toBe(400_000.0)
        ->and($result['rows'][1]['first_price'])->toBe(1100.0)
        ->and($result['rows'][1]['final_price'])->toBe(1045.0)
        ->and($result['rows'][2]['first_price'])->toBe(1800.0)
        ->and($result['rows'][2]['final_price'])->toBe(1980.0)
        ->and($result['meter_price'])->toBe(1419.0);
});

it('resolves area grid rows by kind and subtype with default fallback', function () {
    $villa = ValuationFieldGrid::rows('سكني', 'فيلا');
    $land = ValuationFieldGrid::rows('أرض', 'أرض');

    expect(array_column($villa, 'key'))->toContain('land_area', 'floor_g', 'basement')
        ->and(array_column($land, 'key'))->toBe(['land_area', 'base_area'])
        ->and(ValuationFieldGrid::rows('غير موجود', 'x'))->toBe([])
        ->and(ValuationFieldGrid::isBuilding('سكني'))->toBeTrue()
        ->and(ValuationFieldGrid::isBuilding('أرض'))->toBeFalse();
});
