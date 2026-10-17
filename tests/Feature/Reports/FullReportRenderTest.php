<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportDataBuilder;
use App\Services\Valuation\ValuationReportPdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;

uses(RefreshDatabase::class);

beforeEach(fn () => Http::fake(['*' => Http::response('', 503)]));

function renderFullReport(ReportExportVariant $variant = ReportExportVariant::Full, ?ValuationRequest $request = null): string
{
    $data = app(ValuationReportDataBuilder::class)->build($request ?? reportFixture(), $variant);

    return view('pdf.report.full', $data)->render();
}

function certificateFixture(): ValuationRequest
{
    $request = reportFixture();
    $request->property?->update(['print_valuation_certificate' => true]);

    return $request->refresh();
}

function pageHeaderAndFooter(string $html): string
{
    $start = (int) strpos($html, '<htmlpageheader');
    $end = (int) strpos($html, '</htmlpagefooter>');

    return substr($html, $start, $end - $start);
}

it('defines the page header and footer with page numbers', function () {
    $chrome = pageHeaderAndFooter(renderFullReport());

    expect($chrome)->toContain('<htmlpageheader name="page-header">')
        ->and($chrome)->toContain('<htmlpagefooter name="page-footer">')
        ->and($chrome)->toContain('{nbpg}/{PAGENO}')
        ->and($chrome)->toContain('شركة التقييم للاختبار')
        ->and($chrome)->toContain(__('pdf.Evaluation and Studies Department'))
        ->and($chrome)->toContain('1110000001')
        ->and($chrome)->toContain('1010000001')
        ->and($chrome)->toContain(__('pdf.real_estate_appraisal'))
        ->and($chrome)->toContain('عميل الاختبار');
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
    View::prependLocation(__DIR__.'/views');
    $arabicTitle = trans('pdf.real_estate_appraisal', [], 'ar');
    app()->setLocale('en');

    $prepared = app(ValuationReportPdfGenerator::class)->prepare(reportFixture(), ReportExportVariant::Full);

    expect($prepared['html'])->toContain($arabicTitle)
        ->and($prepared['html'])->not->toContain(trans('pdf.real_estate_appraisal', [], 'en'))
        ->and(app()->getLocale())->toBe('en');
});

it('renders the valuation certificate only when the property asks for it', function () {
    $request = certificateFixture();
    $html = renderFullReport(request: $request);
    $request->property?->update(['print_valuation_certificate' => false]);

    expect(renderFullReport(request: $request->refresh()))->not->toContain('class="rpt-certificate"')
        ->and($html)->toContain('class="rpt-certificate"')
        ->and($html)->toContain(__('pdf.real_estate_appraisal_certificate'))
        ->and($html)->toContain('مالك الاختبار')
        ->and($html)->toContain(e(__('pdf.certification_and_disclaimer_des', ['company' => 'شركة التقييم للاختبار'])))
        ->and($html)->toContain(e(__('pdf.based_on_studies', ['company' => 'شركة التقييم للاختبار'])))
        ->and($html)->toContain(__('pdf.based_on_application'))
        ->and($html)->toContain(__('pdf.inspection_inside_outside'))
        ->and($html)->toContain('2017-01-01')
        ->and($html)->toContain('2027-09-15')
        ->and($html)->toContain('أساسي')
        ->and($html)->toContain('عقار')
        ->and($html)->not->toContain(__('pdf.branch_and_category'));
});

it('omits the certificate company dates that are not set', function () {
    $request = certificateFixture();
    $request->company?->update(['membership_date' => null]);
    $request->update(['state' => 'review']);

    $html = renderFullReport(request: $request->refresh());

    expect($html)->toContain('class="rpt-certificate"')
        ->and($html)->not->toContain(__('pdf.membership_date'))
        ->and($html)->not->toContain('2027-09-15');
});

it('renders one full-width image page per static page', function () {
    $html = renderFullReport();

    expect(substr_count($html, 'class="rpt-static-page"'))->toBe(2);
});

it('renders the executive summary as two cards', function () {
    $html = renderFullReport();

    expect(substr_count($html, 'rpt-executive-summary"'))->toBe(2)
        ->and($html)->toContain(e(__('pdf.subject_to_the_report_des')))
        ->and($html)->toContain(e(__('pdf.compliance_statement_des')))
        ->and($html)->toContain('مقيم الاختبار')
        ->and($html)->toContain('1210000001')
        ->and($html)->toContain('1210000003')
        ->and($html)->toContain('2026/10/01');
});

it('orders the cover, certificate, static pages and summary before the closing page', function () {
    $html = renderFullReport(request: certificateFixture());

    $positions = array_map(fn (string $marker): int|false => strpos($html, $marker), [
        'class="rpt-cover-page"', 'class="rpt-certificate"', 'class="rpt-static-page"',
        'rpt-executive-summary"', 'class="rpt-closing-page"',
    ]);
    $sorted = $positions;
    sort($sorted);

    expect($positions)->not->toContain(false)->and($positions)->toBe($sorted);
});

it('builds the certificate and summary data', function () {
    $request = certificateFixture();
    $request->update(['started_at' => '2026-09-20 10:00:00']);
    $data = app(ValuationReportDataBuilder::class)->build($request->refresh(), ReportExportVariant::Full);

    expect($data['certificate'])->toHaveKeys(['owner_name', 'rows', 'approaches', 'approach_note', 'approved'])
        ->and($data['certificate']['owner_name'])->toBe('مالك الاختبار')
        ->and($data['certificate']['approved'])->toBeTrue()
        ->and(array_column($data['certificate']['approaches'], 'label'))->toBe([
            __('pdf.market_valuation_way_main'), __('pdf.income_valuation_way_main'), __('pdf.cost_valuation'),
        ])
        ->and(array_column($data['certificate']['rows'], 'label'))->toContain(__('pdf.evaluation_date'), __('pdf.property_inspection'))
        ->and($data['summary']['team'])->toBe([
            'evaluator_name' => 'مقيم الاختبار',
            'evaluator_membership' => '1210000001',
            'company_name' => 'شركة التقييم للاختبار',
            'approver_membership' => '1210000003',
        ])
        ->and(array_column($data['summary']['rows'], 'value', 'label'))->toMatchArray([
            __('pdf.inspection_date') => '2026/09/20',
            __('pdf.evaluation_date') => '2026/10/01',
            __('pdf.report_issue_date') => now()->format('Y/m/d'),
            __('pdf.valuation_currency') => __('pdf.saudi_riyal'),
        ])
        ->and(array_column($data['summary']['continued'], 'value', 'label'))->toMatchArray([
            __('pdf.resident_identity') => 'شركة التقييم للاختبار :- '.__('pdf.resident_identity_des'),
            __('pdf.customer_name') => 'عميل الاختبار',
            __('pdf.report_user_name') => 'عميل الاختبار',
        ]);

    $request->property?->update(['print_valuation_certificate' => false]);
    expect(app(ValuationReportDataBuilder::class)->build($request->refresh(), ReportExportVariant::Full)['certificate'])->toBeNull();
});

it('only builds static pages for the full variants', function () {
    $builder = app(ValuationReportDataBuilder::class);
    $request = reportFixture();

    expect($builder->build($request, ReportExportVariant::Enforcement)['static_pages'])->toBe([])
        ->and($builder->build($request, ReportExportVariant::FullDraft)['static_pages'])->toHaveCount(2);
});
