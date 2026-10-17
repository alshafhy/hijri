<?php

declare(strict_types=1);

use App\Support\Valuation\StaticMapImage;
use Illuminate\Support\Facades\Http;

function comparablesTilePng(): string
{
    $image = imagecreatetruecolor(256, 256);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 200, 220, 240));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

/**
 * Runs the test body and deletes only the comparables cache files it created.
 */
function withComparablesCacheCleanup(Closure $body): void
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
    Http::fake(['tile.openstreetmap.org/*' => Http::response(comparablesTilePng())]);
});

it('renders a comparables map with the subject and every valid point', fn () => withComparablesCacheCleanup(function () {
    $uri = (new StaticMapImage)->comparablesDataUri(
        ['lat' => 24.613579, 'lng' => 46.713579],
        [
            ['label' => '1', 'lat' => 24.617531, 'lng' => 46.719753],
            ['label' => '2', 'lat' => 24.609753, 'lng' => 46.708642],
            ['label' => '3', 'lat' => 0, 'lng' => 0],
        ],
    );

    expect($uri)->toStartWith('data:image/png;base64,');
    $size = getimagesizefromstring(base64_decode(substr((string) $uri, 22)));
    expect($size[0])->toBe(650)->and($size[1])->toBe(400)->and($size['mime'])->toBe('image/png');
}));

it('serves a repeated comparables map from the cache without tile requests', fn () => withComparablesCacheCleanup(function () {
    $map = new StaticMapImage;
    $subject = ['lat' => 24.624681, 'lng' => 46.724681];
    $points = [['label' => '1', 'lat' => 24.628642, 'lng' => 46.731975]];

    $first = $map->comparablesDataUri($subject, $points);
    $requests = Http::recorded()->count();
    $second = $map->comparablesDataUri($subject, $points);

    expect($requests)->toBeGreaterThan(0)
        ->and(Http::recorded()->count())->toBe($requests)
        ->and($second)->toBe($first);
}));

it('returns null when neither the subject nor any comparable has a valid point', function () {
    $uri = (new StaticMapImage)->comparablesDataUri(
        ['lat' => 0, 'lng' => 0],
        [['label' => '1', 'lat' => 'abc', 'lng' => 46.7], ['label' => '2', 'lat' => 0, 'lng' => 0]],
    );

    expect($uri)->toBeNull();
    Http::assertNothingSent();
});
