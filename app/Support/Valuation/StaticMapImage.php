<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use Closure;
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

    private const MAX_ZOOM = 16;

    private const MIN_ZOOM = 3;

    private const PADDING = 40;

    private const SUBJECT_LABEL = 'P';

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
        return $this->toDataUri($this->path($lat, $lng, $source, $zoom, $width, $height));
    }

    public function path(mixed $lat, mixed $lng, string $source, int $zoom = 16, int $width = 640, int $height = 360): ?string
    {
        if (! isset(self::SOURCES[$source]) || ! self::isValidPoint($lat, $lng)) {
            return null;
        }

        $lat = round((float) $lat, 6);
        $lng = round((float) $lng, 6);
        $cache = storage_path("app/private/report-map-cache/{$source}-{$zoom}-{$width}x{$height}-{$lat}_{$lng}.png");

        return $this->cached($cache, fn (): ?string => $this->render($lat, $lng, $source, $zoom, $width, $height));
    }

    /**
     * Streets map showing the subject property (red "P") and numbered comparables.
     *
     * @param  array{lat: mixed, lng: mixed}  $subject
     * @param  list<array{label: string, lat: mixed, lng: mixed}>  $points
     * @return string|null PNG data URI, or null when no point is valid or tiles are unavailable
     */
    public function comparablesDataUri(array $subject, array $points, int $width = 650, int $height = 400): ?string
    {
        $markers = $this->markers($subject, $points);
        if ($markers === []) {
            return null;
        }

        $zoom = $this->fittingZoom($markers, $width, $height);
        $key = md5((string) json_encode([self::STREETS, $zoom, $width, $height, $markers]));
        $cache = storage_path("app/private/report-map-cache/comparables-{$key}.png");

        return $this->toDataUri($this->cached($cache, fn (): ?string => $this->renderMarkers($markers, $zoom, $width, $height)));
    }

    private function toDataUri(?string $path): ?string
    {
        return $path !== null ? 'data:image/png;base64,'.base64_encode((string) file_get_contents($path)) : null;
    }

    /**
     * @param  Closure(): ?string  $render
     */
    private function cached(string $cache, Closure $render): ?string
    {
        if (is_file($cache) && filesize($cache) > 0) {
            return $cache;
        }

        try {
            $png = $render();
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

    /**
     * @param  array{lat: mixed, lng: mixed}  $subject
     * @param  list<array{label: string, lat: mixed, lng: mixed}>  $points
     * @return list<array{label: string, lat: float, lng: float, subject: bool}>
     */
    private function markers(array $subject, array $points): array
    {
        $candidates = [['label' => self::SUBJECT_LABEL, 'lat' => $subject['lat'] ?? null, 'lng' => $subject['lng'] ?? null, 'subject' => true]];
        foreach ($points as $point) {
            $candidates[] = ['label' => (string) ($point['label'] ?? ''), 'lat' => $point['lat'] ?? null, 'lng' => $point['lng'] ?? null, 'subject' => false];
        }

        $markers = [];
        foreach ($candidates as $candidate) {
            if (! self::isValidPoint($candidate['lat'], $candidate['lng'])) {
                continue;
            }
            $markers[] = [
                'label' => $candidate['label'],
                'lat' => round((float) $candidate['lat'], 6),
                'lng' => round((float) $candidate['lng'], 6),
                'subject' => $candidate['subject'],
            ];
        }

        return $markers;
    }

    /**
     * @param  list<array{label: string, lat: float, lng: float, subject: bool}>  $markers
     */
    private function fittingZoom(array $markers, int $width, int $height): int
    {
        for ($zoom = self::MAX_ZOOM; $zoom > self::MIN_ZOOM; $zoom--) {
            $box = $this->pixelBounds($markers, $zoom);
            if ($width - 2 * self::PADDING >= $box['maxX'] - $box['minX']
                && $height - 2 * self::PADDING >= $box['maxY'] - $box['minY']) {
                return $zoom;
            }
        }

        return self::MIN_ZOOM;
    }

    /**
     * @param  list<array{label: string, lat: float, lng: float, subject: bool}>  $markers
     * @return array{minX: float, minY: float, maxX: float, maxY: float}
     */
    private function pixelBounds(array $markers, int $zoom): array
    {
        $xs = [];
        $ys = [];
        foreach ($markers as $marker) {
            [$xs[], $ys[]] = $this->worldPixel($marker['lat'], $marker['lng'], $zoom);
        }

        return ['minX' => min($xs), 'minY' => min($ys), 'maxX' => max($xs), 'maxY' => max($ys)];
    }

    /**
     * @param  list<array{label: string, lat: float, lng: float, subject: bool}>  $markers
     */
    private function renderMarkers(array $markers, int $zoom, int $width, int $height): ?string
    {
        $box = $this->pixelBounds($markers, $zoom);
        [$lat, $lng] = $this->worldPoint(($box['minX'] + $box['maxX']) / 2, ($box['minY'] + $box['maxY']) / 2, $zoom);

        $map = $this->stitch($lat, $lng, self::STREETS, $zoom, $width, $height);
        if ($map === null) {
            return null;
        }

        usort($markers, static fn (array $a, array $b): int => $a['subject'] <=> $b['subject']);
        foreach ($markers as $marker) {
            [$x, $y] = ($map['project'])($marker['lat'], $marker['lng']);
            $this->drawLabelledMarker($map['canvas'], $x, $y, $marker['label'], $marker['subject']);
        }

        return $this->encode($map['canvas']);
    }

    private function render(float $lat, float $lng, string $source, int $zoom, int $width, int $height): ?string
    {
        $map = $this->stitch($lat, $lng, $source, $zoom, $width, $height);
        if ($map === null) {
            return null;
        }

        [$x, $y] = ($map['project'])($lat, $lng);
        $this->drawMarker($map['canvas'], $x, $y);

        return $this->encode($map['canvas']);
    }

    /**
     * Stitches tiles into a canvas centred on the point and returns it with a lat/lng -> canvas pixel projection.
     *
     * @return array{canvas: \GdImage, project: Closure(float, float): array{int, int}}|null
     */
    private function stitch(float $lat, float $lng, string $source, int $zoom, int $width, int $height): ?array
    {
        [$centerX, $centerY] = $this->worldPixel($lat, $lng, $zoom);
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

        $project = function (float $pointLat, float $pointLng) use ($zoom, $left, $top): array {
            [$x, $y] = $this->worldPixel($pointLat, $pointLng, $zoom);

            return [(int) round($x - $left), (int) round($y - $top)];
        };

        return ['canvas' => $canvas, 'project' => $project];
    }

    /**
     * Web Mercator world pixel coordinates at the given zoom.
     *
     * @return array{float, float}
     */
    private function worldPixel(float $lat, float $lng, int $zoom): array
    {
        $worldSize = self::TILE * (2 ** $zoom);
        $latRad = deg2rad($lat);

        return [
            ($lng + 180) / 360 * $worldSize,
            (1 - log(tan($latRad) + 1 / cos($latRad)) / M_PI) / 2 * $worldSize,
        ];
    }

    /**
     * @return array{float, float} lat, lng for a world pixel at the given zoom
     */
    private function worldPoint(float $x, float $y, int $zoom): array
    {
        $worldSize = self::TILE * (2 ** $zoom);

        return [
            rad2deg(atan(sinh(M_PI * (1 - 2 * $y / $worldSize)))),
            $x / $worldSize * 360 - 180,
        ];
    }

    private function encode(\GdImage $canvas): string
    {
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

    private function drawLabelledMarker(\GdImage $canvas, int $x, int $y, string $label, bool $subject): void
    {
        $white = (int) imagecolorallocate($canvas, 255, 255, 255);
        $fill = $subject
            ? (int) imagecolorallocate($canvas, 220, 38, 38)
            : (int) imagecolorallocate($canvas, 0x1F, 0x38, 0x64);

        imagefilledellipse($canvas, $x, $y, 30, 30, $white);
        imagefilledellipse($canvas, $x, $y, 26, 26, $fill);

        $font = 5;
        $textX = $x - intdiv(imagefontwidth($font) * strlen($label), 2);
        $textY = $y - intdiv(imagefontheight($font), 2);
        imagestring($canvas, $font, $textX, $textY, $label, $white);
    }
}
