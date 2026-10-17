<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Support\Pdf\ReportRenderOptions;

interface ReportPdfRenderer
{
    public function render(string $html, ReportRenderOptions $options): string;
}
