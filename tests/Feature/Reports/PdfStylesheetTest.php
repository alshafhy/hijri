<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Services\Valuation\ValuationReportPdfGenerator;
use App\Support\Pdf\PdfStylesheet;

it('reads a PDF stylesheet', function () {
    expect(PdfStylesheet::read(resource_path('css/pdf/documents.css')))->toContain('body');
});

it('fails loudly when a PDF stylesheet is missing', function () {
    PdfStylesheet::read(resource_path('css/pdf/missing.css'));
})->throws(RuntimeException::class);

it('refuses to render a valuation report without its stylesheet', function () {
    (new ValuationReportPdfGenerator(cssPath: resource_path('css/pdf/missing.css')))
        ->renderBytes('<p>x</p>', ReportExportVariant::Full);
})->throws(RuntimeException::class);
