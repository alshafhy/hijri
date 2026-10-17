<?php

declare(strict_types=1);

use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Models\PropertyTotal;
use App\Support\Valuation\ComparablesCalculator;
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
    $rows = collect($adjustments['rows'])->keyBy('key');

    expect($adjustments['columns'])->toBe([1, 2, 3])
        ->and($adjustments['rows'])->toHaveCount(21)
        ->and($adjustments['rows'][0]['key'])->toBe('transaction_date')
        ->and($adjustments['rows'][0]['label'])->toBe(__('pdf.transaction date'))
        ->and($rows['transaction_date']['values'])->toBe(['2026-09-01', '2026-09-02', '2026-09-03'])
        ->and($rows['market_conditions']['percentages'])->toBe(['5%', '3%', '0%'])
        ->and($rows['transaction_subtotal']['values'])->toBe(['5%', '3%', '0%'])
        ->and($rows['previous_settlement_value']['label'])->toBe(__('pdf.The value of previous settlement percentages'))
        ->and($rows['previous_settlement_value']['values'])->toBe(['150.00', '84.00', '0.00'])
        ->and($rows['price_after_transaction']['values'])->toBe(['3,150.00', '2,884.00', '3,100.00'])
        ->and($rows['area']['values'])->toBe(['500.00', '600.00', '550.00'])
        ->and($rows['area']['percentages'])->toBe(['-2%', '0%', '2%'])
        ->and($rows['location_status']['values'])->toBe(['ممتاز', 'ممتاز', 'ممتاز'])
        ->and($rows['property_total']['values'])->toBe(['-2%', '0%', '2%'])
        ->and($rows['total_adjustments_price']['values'])->toBe(['3,087.00', '2,884.00', '3,162.00'])
        ->and($rows['proportional_adjustment']['values'])->toBe(['40%', '30%', '30%']);

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
        ->and(collect($section['adjustments']['rows'])->firstWhere('key', 'transaction_subtotal')['values'][3])->toBe('4%');
});

it('includes the settlement ratio in the transaction stage like the calculator', function () {
    $property = reportFixture()->property;
    PropertyAdjustment::query()->where('property_id', $property->id)->where('type', 1)->update(['settlement_ratio' => 2]);
    $property->refresh();

    $section = (new ComparablesSection)->build($property);
    $rows = collect($section['adjustments']['rows'])->keyBy('key');
    $adjustment = $property->adjustments->firstWhere('type', 1);
    $comparable = $property->comparables->firstWhere('sequence', 1);
    $expected = (new ComparablesCalculator)->calculate([1 => $adjustment->attributesToArray() + ['area' => $comparable->area, 'price' => $comparable->price]])['rows'][1];

    expect($expected['first_price'])->toBe(3210.0)
        ->and($rows['transaction_date']['percentages'][0])->toBe('2%')
        ->and($rows['transaction_subtotal']['values'][0])->toBe('5%')
        ->and($rows['previous_settlement_value']['values'][0])->toBe(number_format($expected['first_value'], 2))
        ->and($rows['price_after_transaction']['values'][0])->toBe(number_format($expected['first_price'], 2))
        ->and($rows['total_adjustments_value']['values'][0])->toBe(number_format($expected['first_value'] + $expected['second_value'], 2))
        ->and($rows['total_adjustments_price']['values'][0])->toBe('3,145.80')
        ->and($section['settlement']['final_meter_prices'][0])->toBe(round($expected['final_price'], 2))
        ->and($section['settlement']['final_meter_prices'][0])->toBe(3145.8);
});

it('parses map coordinates from @, q=, ll= and plain formats and rejects out-of-range ones', function (string $url, ?array $expected) {
    expect((new ComparablesSection)->parseCoordinates($url))->toBe($expected);
})->with([
    '@ path' => ['https://www.google.com/maps/@24.711,46.671,17z', [24.711, 46.671]],
    'q= query' => ['https://maps.google.com/?q=24.72,46.68', [24.72, 46.68]],
    'll= query' => ['https://maps.google.com/maps?z=15&ll=-33.8688,151.2093', [-33.8688, 151.2093]],
    'plain pair' => [' 24.7, 46.6 ', [24.7, 46.6]],
    'out of range' => ['https://maps.google.com/?q=95.5,46.6', null],
    'short link' => ['https://maps.app.goo.gl/xyz', null],
]);

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
