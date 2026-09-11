<?php

declare(strict_types=1);

namespace App\Services\Valuation;

use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

/**
 * Disk cache for generated valuation report PDFs (legacy ReportPdfCacheService port).
 */
final class ReportPdfCacheService
{
    private const CACHE_VERSION = 'v1';

    private const VARIANT_LEN = 8;

    public function enabled(): bool
    {
        return (bool) config('report_pdf.enabled', true);
    }

    /**
     * @param  array<string, mixed>  $variant
     */
    public function key(string $html, array $variant = []): string
    {
        $variantId = substr(hash('sha256', json_encode($variant, JSON_UNESCAPED_UNICODE)), 0, self::VARIANT_LEN);

        $content = hash('sha256', implode('|', [
            self::CACHE_VERSION,
            $html,
            $this->assetStamp($html),
            $variantId,
        ]));

        return $variantId.$content;
    }

    public function get(int $valuationRequestId, string $key): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        $path = $this->path($valuationRequestId, $key);

        return is_file($path) && filesize($path) > 0 ? $path : null;
    }

    public function put(int $valuationRequestId, string $key, string $bytes, string $filename = ''): ?string
    {
        if (! $this->enabled() || $bytes === '') {
            return null;
        }

        $dir = $this->directory($valuationRequestId);
        File::ensureDirectoryExists($dir, 0775);

        $path = $this->path($valuationRequestId, $key);
        $tmp = $path.'.'.getmypid().'.part';

        if (@file_put_contents($tmp, $bytes) === false || ! @rename($tmp, $path)) {
            @unlink($tmp);

            return null;
        }

        if ($filename !== '') {
            @file_put_contents($this->metaPath($valuationRequestId, $key), $filename);
        }

        $this->forgetOthers($valuationRequestId, $key);

        return $path;
    }

    public function filename(int $valuationRequestId, string $key): ?string
    {
        $meta = $this->metaPath($valuationRequestId, $key);

        return is_file($meta) ? (trim((string) file_get_contents($meta)) ?: null) : null;
    }

    public function downloadResponse(string $bytes, string $filename): Response
    {
        return response($bytes, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'public, must-revalidate, max-age=0',
            'Pragma' => 'public',
            'Content-Length' => (string) strlen($bytes),
        ]);
    }

    public function isGenerating(int $valuationRequestId, string $key): bool
    {
        $lock = $this->lockPath($valuationRequestId, $key);

        if (! is_file($lock)) {
            return false;
        }

        if (time() - filemtime($lock) > (int) config('report_pdf.lock_ttl', 600)) {
            @unlink($lock);

            return false;
        }

        return true;
    }

    public function markGenerating(int $valuationRequestId, string $key): void
    {
        File::ensureDirectoryExists($this->directory($valuationRequestId), 0775);
        @touch($this->lockPath($valuationRequestId, $key));
    }

    public function clearGenerating(int $valuationRequestId, string $key): void
    {
        @unlink($this->lockPath($valuationRequestId, $key));
    }

    private function directory(int $valuationRequestId): string
    {
        return storage_path('app/private/report-pdf-cache/'.$valuationRequestId);
    }

    private function path(int $valuationRequestId, string $key): string
    {
        return $this->directory($valuationRequestId).'/'.$key.'.pdf';
    }

    private function lockPath(int $valuationRequestId, string $key): string
    {
        return $this->directory($valuationRequestId).'/'.$key.'.lock';
    }

    private function metaPath(int $valuationRequestId, string $key): string
    {
        return $this->directory($valuationRequestId).'/'.$key.'.name';
    }

    private function forgetOthers(int $valuationRequestId, string $keepKey): void
    {
        $variant = substr($keepKey, 0, self::VARIANT_LEN);

        foreach (glob($this->directory($valuationRequestId).'/'.$variant.'*.{pdf,name}', GLOB_BRACE) ?: [] as $file) {
            $key = preg_replace('/\.(pdf|name)$/', '', basename($file));
            if ($key !== $keepKey) {
                @unlink($file);
            }
        }
    }

    private function assetStamp(string $html): string
    {
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $html, $imgs);
        preg_match_all('/url\(\s*[\'"]?([^)\'"]+)[\'"]?\s*\)/i', $html, $urls);

        $stamp = [];
        foreach (array_unique(array_merge($imgs[1], $urls[1])) as $src) {
            $src = html_entity_decode($src);
            if ($src === '' || preg_match('#^(https?:|data:)#i', $src)) {
                continue;
            }
            $path = str_starts_with($src, '/') ? $src : public_path($src);
            $stamp[] = is_file($path) ? $src.':'.filesize($path).':'.filemtime($path) : $src.':missing';
        }

        sort($stamp);

        return implode(';', $stamp);
    }
}
