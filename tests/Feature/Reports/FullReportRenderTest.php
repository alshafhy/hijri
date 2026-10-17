<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Services\Valuation\ValuationReportDataBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => Http::fake(['*' => Http::response('', 503)]));

function renderFullReport(ReportExportVariant $variant = ReportExportVariant::Full): string
{
    $data = app(ValuationReportDataBuilder::class)->build(reportFixture(), $variant);

    return view('pdf.report.full', $data)->render();
}

it('defines the page header and footer with page numbers', function () {
    $html = renderFullReport();

    expect($html)->toContain('<htmlpageheader name="page-header">')
        ->and($html)->toContain('<htmlpagefooter name="page-footer">')
        ->and($html)->toContain('{PAGENO}/{nbpg}')
        ->and($html)->toContain('شركة التقييم للاختبار')
        ->and($html)->toContain(__('pdf.Evaluation and Studies Department'))
        ->and($html)->toContain('1110000001')
        ->and($html)->toContain('1010000001')
        ->and($html)->toContain(__('pdf.real_estate_appraisal'))
        ->and($html)->toContain('عميل الاختبار');
});

it('renders the cover page rows', function () {
    $html = renderFullReport();

    expect($html)->toContain('class="rpt-cover-page"')
        ->and($html)->toContain('77001')
        ->and($html)->toContain(__('pdf.hijri_suffix'))
        ->and($html)->toContain('DEP-1')
        ->and($html)->toContain('310101010101')
        ->and($html)->toContain('فيلا في الرياض')
        ->and($html)->toContain('الملقا')
        ->and($html)->toContain(__('pdf.asset_sector_usage'))
        ->and($html)->toContain(__('pdf.resident_identity'));
});

it('renders the closing page last', function () {
    $html = renderFullReport();

    expect($html)->toContain('class="rpt-closing-page"')
        ->and(strrpos($html, 'rpt-closing-page'))->toBeGreaterThan(strrpos($html, 'rpt-cover-page'));
});

it('builds the cover data from the request and property', function () {
    $data = app(ValuationReportDataBuilder::class)->build(reportFixture(), ReportExportVariant::Full);

    expect($data['cover'])->toBe([
        'customer_name' => 'عميل الاختبار',
        'property_kind' => 'سكني',
        'property_type' => 'فيلا',
        'instrument_no' => '310101010101',
        'deposit_number' => 'DEP-1',
        'origin_sector' => 'سكني',
    ]);
});

it('renders arabic labels for arabic variants regardless of the app locale', function () {
    app()->setLocale('en');

    expect(ReportExportVariant::Full->locale())->toBe('ar')
        ->and(ReportExportVariant::FullDraft->locale())->toBe('ar')
        ->and(ReportExportVariant::Enforcement->locale())->toBe('ar')
        ->and(ReportExportVariant::FullEnglish->locale())->toBe('en');
});

it('only builds static pages for the full variants', function () {
    $builder = app(ValuationReportDataBuilder::class);
    $request = reportFixture();

    expect($builder->build($request, ReportExportVariant::Enforcement)['static_pages'])->toBe([])
        ->and($builder->build($request, ReportExportVariant::FullDraft)['static_pages'])->toHaveCount(2);
});
