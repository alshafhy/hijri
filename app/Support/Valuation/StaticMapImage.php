<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Renders a static map (streets or aerial) centred on a point by stitching web-map tiles,
 * caching the PNG on disk so each location is downloaded only once.
 */
final class StaticMapImage
{
    public const STREETS = 'streets';

    public const AERIAL = 'aerial';

    private const TILE = 256;

    /** @var array<string, string> */
    private const SOURCES = [
        self::STREETS => 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
        self::AERIAL => 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
    ];

    public static function isValidPoint(mixed $lat, mixed $lng): bool
    {
        return is_numeric($lat) && is_numeric($lng)
            && (float) $lat !== 0.0 && (float) $lng !== 0.0
            && abs((float) $lat) <= 85 && abs((float) $lng) <= 180;
    }

    /**
     * @return string|null PNG data URI, or null when the point is invalid or tiles are unavailable
     */
    public function dataUri(mixed $lat, mixed $lng, string $source, int $zoom = 16, int $width = 640, int $height = 360): ?string
    {
        $path = $this->path($lat, $lng, $source, $zoom, $width, $height);

        return $path !== null ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path)) : null;
    }

    public function path(mixed $lat, mixed $lng, string $source, int $zoom = 16, int $width = 640, int $height = 360): ?string
    {
        if (! isset(self::SOURCES[$source]) || ! self::isValidPoint($lat, $lng)) {
            return null;
        }

        $lat = round((float) $lat, 6);
        $lng = round((float) $lng, 6);
        $cache = storage_path("app/private/report-map-cache/{$source}-{$zoom}-{$width}x{$height}-{$lat}_{$lng}.png");

        if (is_file($cache) && filesize($cache) > 0) {
            return $cache;
        }

        try {
            $png = $this->render($lat, $lng, $source, $zoom, $width, $height);
        } catch (Throwable) {
            return null;
        }

        if ($png === null) {
            return null;
        }

        File::ensureDirectoryExists(dirname($cache));
        file_put_contents($cache, $png);

        return $cache;
    }

    private function render(float $lat, float $lng, string $source, int $zoom, int $width, int $height): ?string
    {
        $worldSize = self::TILE * (2 ** $zoom);
        $centerX = ($lng + 180) / 360 * $worldSize;
        $latRad = deg2rad($lat);
        $centerY = (1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $worldSize;

        $left = (int) floor($centerX - $width / 2);
        $top = (int) floor($centerY - $height / 2);

        $canvas = imagecreatetruecolor($width, $height);

        for ($tileX = intdiv($left, self::TILE); $tileX * self::TILE < $left + $width; $tileX++) {
            for ($tileY = intdiv($top, self::TILE); $tileY * self::TILE < $top + $height; $tileY++) {
                $tile = $this->tile($source, $zoom, $tileX, $tileY);
                if ($tile === null) {
                    imagedestroy($canvas);

                    return null;
                }
                imagecopy($canvas, $tile, $tileX * self::TILE - $left, $tileY * self::TILE - $top, 0, 0, self::TILE, self::TILE);
                imagedestroy($tile);
            }
        }

        $this->drawMarker($canvas, (int) round($centerX - $left), (int) round($centerY - $top));

        ob_start();
        imagepng($canvas, null, 6);
        imagedestroy($canvas);

        return (string) ob_get_clean();
    }

    private function tile(string $source, int $zoom, int $x, int $y): ?\GdImage
    {
        $url = strtr(self::SOURCES[$source], ['{z}' => (string) $zoom, '{x}' => (string) $x, '{y}' => (string) $y]);

        $response = Http::timeout(10)
            ->withHeaders(['User-Agent' => config('app.name').' report generator ('.config('app.url').')'])
            ->get($url);

        if (! $response->successful()) {
            return null;
        }

        $image = @imagecreatefromstring($response->body());

        return $image instanceof \GdImage ? $image : null;
    }

    private function drawMarker(\GdImage $canvas, int $x, int $y): void
    {
        $white = (int) imagecolorallocate($canvas, 255, 255, 255);
        $red = (int) imagecolorallocate($canvas, 220, 38, 38);

        imagefilledellipse($canvas, $x, $y, 26, 26, $white);
        imagefilledellipse($canvas, $x, $y, 20, 20, $red);
        imagefilledellipse($canvas, $x, $y, 6, 6, $white);
    }
}
