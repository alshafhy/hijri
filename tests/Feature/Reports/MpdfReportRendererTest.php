<?php

declare(strict_types=1);

use App\Contracts\ReportPdfRenderer;
use App\Support\Pdf\ReportRenderOptions;

it('renders Arabic RTL html to PDF bytes', function () {
    $bytes = app(ReportPdfRenderer::class)->render(
        '<table><tr><td>المدينة</td><td>الرياض</td></tr></table>',
        new ReportRenderOptions(direction: 'rtl'),
    );

    expect($bytes)->toStartWith('%PDF')->and(strlen($bytes))->toBeGreaterThan(1000);
});

it('embeds the configured Arabic font and the watermark', function () {
    $bytes = app(ReportPdfRenderer::class)->render('<p>نص</p>', new ReportRenderOptions(direction: 'rtl', watermark: 'مسودة'));

    expect($bytes)->toContain('DINNextLTArabic')
        ->and($bytes)->toContain('/ExtGState');
});
