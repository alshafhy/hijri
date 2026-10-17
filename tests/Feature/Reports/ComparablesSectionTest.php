<?php

declare(strict_types=1);

use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Models\PropertyTotal;
use App\Support\Valuation\Report\ComparablesSection;
use App\Support\Valuation\StaticMapImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function comparablesSectionTilePng(): string
{
    $image = imagecreatetruecolor(256, 256);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 210, 230, 210));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

/**
 * Runs the test body and deletes only the comparables cache files it created.
 */
function withComparablesSectionCacheCleanup(Closure $body): void
{
    $files = fn (): array => glob(storage_path('app/private/report-map-cache/comparables-*.png')) ?: [];
    $before = $files();

    try {
        $body();
    } finally {
        array_map('unlink', array_diff($files(), $before));
    }
}

beforeEach(function () {
    app()->setLocale('ar');
    Http::fake(['tile.openstreetmap.org/*' => Http::response(comparablesSectionTilePng())]);
});

it('builds comparables, a 3-column adjustments matrix and the settlement', fn () => withComparablesSectionCacheCleanup(function () {
    $section = (new ComparablesSection)->build(reportFixture()->property);

    expect($section['hidden'])->toBeFalse()
        ->and($section['information_source'])->toBe('منصة السوق')
        ->and($section['map'])->toStartWith('data:image/png;base64,')
        ->and($section['comparables'])->toHaveCount(3)
        ->and($section['comparables'][0])->toBe([
            'sequence' => 1,
            'type' => 'أرض',
            'front_type' => 'شمالية',
            'street_width' => '20',
            'area' => 500.0,
            'price_per_meter' => 3000.0,
            'total' => 1_500_000.0,
            'percentage' => 100.0,
            'coordinates_url' => 'https://www.google.com/maps/@24.711,46.671,17z',
            'transaction_date' => '2026-09-01',
        ]);

    $adjustments = $section['adjustments'];
    $rows = collect($adjustments['rows'])->keyBy('label');

    expect($adjustments['columns'])->toBe([1, 2, 3])
        ->and($adjustments['rows'])->toHaveCount(20)
        ->and($adjustments['rows'][0]['label'])->toBe(__('pdf.transaction date'))
        ->and($adjustments['rows'][0]['values'])->toBe(['2026-09-01', '2026-09-02', '2026-09-03'])
        ->and($rows[__('pdf.market conditions value')]['percentages'])->toBe(['5%', '3%', '0%'])
        ->and($rows[__('pdf.Total settlement percentages')]['values'])->toBe(['5%', '3%', '0%'])
        ->and($adjustments['rows'][5]['values'])->toBe(['3,150.00', '2,884.00', '3,100.00'])
        ->and($rows[__('pdf.space')]['values'])->toBe(['500.00', '600.00', '550.00'])
        ->and($rows[__('pdf.space')]['percentages'])->toBe(['-2%', '0%', '2%'])
        ->and($rows[__('pdf.site attractiveness')]['values'])->toBe(['ممتاز', 'ممتاز', 'ممتاز'])
        ->and($rows[__('pdf.last total adjustments')]['values'])->toBe(['-2%', '0%', '2%'])
        ->and($rows[__('pdf.proportional adjustment')]['values'])->toBe(['40%', '30%', '30%']);

    expect($section['settlement'])->toBe([
        'final_meter_prices' => [3087.0, 2884.0, 3162.0],
        'weights' => [40.0, 30.0, 30.0],
        'weighted_meter_price' => 3050.0,
        'approximate_meter_price' => 3000.0,
    ]);
}));

it('adds a fourth column when a fourth comparable has an adjustment row', function () {
    $property = reportFixture()->property;
    PropertyComparable::query()->create([
        'property_id' => $property->id,
        'sequence' => 4,
        'real_estate_type' => 'أرض',
        'area' => 700,
        'price' => 2900,
        'coordinates_url' => 'https://goo.gl/maps/abc',
    ]);
    PropertyAdjustment::query()->create(['property_id' => $property->id, 'type' => 4, 'sale_terms_percentage' => 4]);

    $section = (new ComparablesSection)->build($property->refresh());

    expect($section['adjustments']['columns'])->toBe([1, 2, 3, 4])
        ->and($section['comparables'])->toHaveCount(4)
        ->and(collect($section['adjustments']['rows'])->every(fn (array $row): bool => count($row['values']) === 4 && count($row['percentages']) === 4))->toBeTrue()
        ->and($section['adjustments']['rows'][4]['values'][3])->toBe('4%');
});

it('respects the hide comparisons flag', function () {
    $property = reportFixture()->property;
    PropertyTotal::query()->where('property_id', $property->id)->update(['hide_comparisons_info_table' => true]);

    $section = (new ComparablesSection)->build($property->refresh());

    expect($section['hidden'])->toBeTrue()
        ->and($section['map'])->toBeNull();
    Http::assertNothingSent();
});

it('omits comparables whose coordinates url cannot be parsed from the map', fn () => withComparablesSectionCacheCleanup(function () {
    $property = reportFixture()->property;
    PropertyComparable::query()->where('property_id', $property->id)->where('sequence', 2)
        ->update(['coordinates_url' => 'https://maps.app.goo.gl/xyz']);

    $section = (new ComparablesSection)->build($property->refresh());

    $expected = (new StaticMapImage)->comparablesDataUri(
        ['lat' => 24.7136, 'lng' => 46.6753],
        [['label' => '1', 'lat' => 24.711, 'lng' => 46.671], ['label' => '3', 'lat' => 24.713, 'lng' => 46.673]],
    );
    $withAll = (new StaticMapImage)->comparablesDataUri(
        ['lat' => 24.7136, 'lng' => 46.6753],
        [['label' => '1', 'lat' => 24.711, 'lng' => 46.671], ['label' => '2', 'lat' => 24.712, 'lng' => 46.672], ['label' => '3', 'lat' => 24.713, 'lng' => 46.673]],
    );

    expect($expected)->toStartWith('data:image/png;base64,')
        ->and($section['map'])->toBe($expected)
        ->and($section['map'] === $withAll)->toBeFalse();
}));
