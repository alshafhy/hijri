<?php

declare(strict_types=1);

use App\Models\PropertyComponent;
use App\Support\Valuation\Report\CostTableSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('maps components to grid titles in grid order with totals', function () {
    $property = reportFixture()->property;

    $table = (new CostTableSection)->build($property, 'ar');

    expect($table['rows'])->toBe([
        ['title' => 'مساحة الأرض', 'area' => 500.0, 'price' => 3000.0, 'total' => 1_500_000.0],
        ['title' => 'الملحق الأرضي', 'area' => 300.0, 'price' => 1500.0, 'total' => 450_000.0],
        ['title' => 'الملحق العلوي', 'area' => 150.0, 'price' => 1200.0, 'total' => 180_000.0],
    ])
        ->and($table['profit'])->toBe(63_000.0)
        ->and($table['depreciation'])->toBe(138_600.0)
        ->and($table['movables'])->toBe(0.0)
        ->and($table['final_amount'])->toBe(2_000_000.0)
        ->and($table['final_in_words'])->toContain('مليون')
        ->and($table['forced_sale'])->toBe(['percentage' => 15, 'amount' => 300_000.0]);
});

it('drops rows that have neither area nor price', function () {
    $property = reportFixture()->property;
    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'basement',
        'area_value' => 0,
        'price_value' => null,
    ]);

    $table = (new CostTableSection)->build($property->refresh(), 'ar');

    expect(array_column($table['rows'], 'title'))->not->toContain('القبو')
        ->and($table['rows'])->toHaveCount(3);
});

it('prints only rows configured in the valuation grid', function () {
    $property = reportFixture()->property;
    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'legacy_extra',
        'area_value' => 40,
        'price_value' => 900,
    ]);

    $table = (new CostTableSection)->build($property->refresh(), 'ar');

    expect(array_column($table['rows'], 'title'))->not->toContain('legacy_extra')
        ->and($table['rows'])->toHaveCount(3);
});

it('hides forced sale unless the value assumption is forced sale', function () {
    $property = reportFixture()->property;
    $property->update(['value_assumption' => 1]);

    $table = (new CostTableSection)->build($property->refresh(), 'ar');

    expect($table['forced_sale'])->toBeNull();
});

it('flags the manual override and falls back to the calculated total', function () {
    $property = reportFixture()->property;
    $section = new CostTableSection;

    expect($section->build($property, 'en'))
        ->toMatchArray(['manual' => true, 'final_amount' => 2_000_000.0])
        ->and($section->build($property, 'en')['final_in_words'])->toContain('million');

    $property->total->update(['total_amount_manual' => null]);
    $table = $section->build($property->refresh(), 'ar');

    expect($table['manual'])->toBeFalse()
        ->and($table['final_amount'])->toBe(2_054_400.0);
});
