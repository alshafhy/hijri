<?php

declare(strict_types=1);

use App\Actions\Valuation\ComputeComponentValuationTotalAction;
use App\Actions\Valuation\ResolveValuationFinalAmountAction;
use App\Models\Property;
use App\Models\PropertyComponent;
use App\Models\PropertyTotal;
use Illuminate\Support\Collection;

it('prefers non-zero total_amount_manual as final amount', function () {
    $total = new PropertyTotal([
        'total_amount' => 1000.0,
        'total_amount_manual' => 777.5,
        'forced_sale_percentage' => 10,
        'forced_sale_amount' => 100.0,
    ]);
    $property = new Property;
    $property->setRelation('total', $total);

    $result = app(ResolveValuationFinalAmountAction::class)->execute($property);

    expect($result['amount'])->toBe(777.5)
        ->and($result['is_manual'])->toBeTrue()
        ->and($result['forced_sale_percentage'])->toBe(10);
});

it('falls back to total_amount when manual is zero', function () {
    $total = new PropertyTotal([
        'total_amount' => 1234.0,
        'total_amount_manual' => 0.0,
    ]);

    $result = app(ResolveValuationFinalAmountAction::class)->execute($total);

    expect($result['amount'])->toBe(1234.0)
        ->and($result['is_manual'])->toBeFalse();
});

it('computes residential multi-component total with importer keys', function () {
    $property = new Property([
        'property_kind' => 'سكني',
        'property_type' => 'عمارة سكنية',
    ]);

    $components = new Collection;
    foreach ([
        ['land_area', 625, 3950],
        ['floor_g', 450, 700],
        ['floor_a', 450, 700],
        ['floor_u', 1350, 700],
        ['annexe', 200, 500],
    ] as [$key, $area, $price]) {
        $components->push(new PropertyComponent([
            'component_key' => $key,
            'area_value' => $area,
            'price_value' => $price,
        ]));
    }
    $property->setRelation('components', $components);

    $total = app(ComputeComponentValuationTotalAction::class)->execute($property);

    expect($total)->toBe(4143750.0);
});
