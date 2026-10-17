<?php

declare(strict_types=1);

namespace App\Support\Pdf;

use RuntimeException;

final class PdfStylesheet
{
    public static function read(string $path): string
    {
        $css = is_readable($path) ? file_get_contents($path) : false;

        if ($css === false) {
            throw new RuntimeException("PDF stylesheet is not readable: {$path}");
        }

        return $css;
    }
}
