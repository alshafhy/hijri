<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use Illuminate\Support\Facades\Storage;

/**
 * Embeds local images as data URIs so report PDFs never depend on file URLs.
 */
final class ReportImage
{
    private const MIMES = ['image/png', 'image/jpeg', 'image/gif', 'image/webp'];

    public static function fromPath(?string $absolutePath): ?string
    {
        if ($absolutePath === null || ! is_file($absolutePath) || filesize($absolutePath) === 0) {
            return null;
        }

        $mime = (string) @mime_content_type($absolutePath);
        if (! in_array($mime, self::MIMES, true)) {
            return null;
        }

        $bytes = @file_get_contents($absolutePath);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($bytes);
    }

    public static function fromDisk(string $disk, ?string $path): ?string
    {
        if ($path === null || $path === '' || ! Storage::disk($disk)->exists($path)) {
            return null;
        }

        return self::fromPath(Storage::disk($disk)->path($path));
    }
}
