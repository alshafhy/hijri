<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('builds a fully populated report fixture', function () {
    $request = reportFixture();
    $property = $request->property;

    expect($request->company)->not->toBeNull()
        ->and($request->evaluator?->membership_number)->toBe('1210000001')
        ->and($request->coordinator)->not->toBeNull()
        ->and($request->subUser)->not->toBeNull()
        ->and($property->location?->x_axis)->toBe('24.7136')
        ->and($property->location?->y_axis)->toBe('46.6753')
        ->and($property->lands)->toHaveCount(1)
        ->and($property->building)->not->toBeNull()
        ->and($property->facades)->toHaveCount(1)
        ->and($property->flooring)->not->toBeNull()
        ->and($property->ac)->not->toBeNull()
        ->and($property->question?->best_use)->toBe(1)
        ->and($property->question?->inside_inspection)->toBe('نعم')
        ->and($property->services)->toHaveCount(1)
        ->and($property->borders)->toHaveCount(1)
        ->and($property->comparables)->toHaveCount(3)
        ->and($property->adjustments)->toHaveCount(3)
        ->and($property->components->pluck('component_key')->sort()->values()->all())->toBe(['ground', 'land_area', 'upper'])
        ->and($property->total?->forced_sale_percentage)->toBe(15)
        ->and($property->total?->isManualOverride())->toBeTrue()
        ->and($request->customTableRows)->toHaveCount(2)
        ->and($request->attachments->pluck('mime_type')->all())->toBe(['image/jpeg', 'application/pdf']);

    foreach ($request->attachments as $attachment) {
        Storage::disk('local')->assertExists($attachment->path);
    }
});
