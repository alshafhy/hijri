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

it('switches the cost rows to the english layout for the english report', function () {
    $property = reportFixture()->property;

    $en = (new CostTableSection)->build($property, 'en');

    expect(array_column($en['rows'], 'title'))->toBe(['total_land_area', 'الملحق الأرضي', 'الملحق العلوي'])
        ->and($en['show_land_rows'])->toBeTrue();

    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'share_land',
        'area_value' => 100,
        'price_value' => 2000,
    ]);
    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'flat_area',
        'area_value' => 80,
        'price_value' => 1500,
    ]);

    $en = (new CostTableSection)->build($property->refresh(), 'en');

    expect(array_column($en['rows'], 'title'))->toBe([
        'total_land_area', 'الملحق الأرضي', 'الملحق العلوي', 'share_land', 'flat_area',
    ]);
});

it('keeps the arabic rows and drops the english-only rows for the arabic report', function () {
    $property = reportFixture()->property;
    $property->update(['property_type' => 'شقة']);
    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'share_land',
        'area_value' => 100,
        'price_value' => 2000,
    ]);
    PropertyComponent::query()->create([
        'property_id' => $property->id,
        'component_key' => 'flat_area',
        'area_value' => 80,
        'price_value' => 1500,
    ]);
    $property = $property->refresh();

    expect(array_column((new CostTableSection)->build($property, 'ar')['rows'], 'title'))
        ->toBe(['نصيبها من الأرض', 'مساحة الشقة'])
        ->and((new CostTableSection)->build($property, 'ar')['show_land_rows'])->toBeFalse();

    expect(array_column((new CostTableSection)->build($property, 'en')['rows'], 'title'))
        ->toBe(['total_land_area', 'share_land', 'flat_area']);
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
