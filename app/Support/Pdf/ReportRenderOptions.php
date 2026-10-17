<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final readonly class ReportRenderOptions
{
    public function __construct(
        public string $direction = 'rtl',
        public ?string $watermark = null,
        public string $css = '',
        public string $paper = 'A4',
        public string $orientation = 'P',
    ) {}
}
