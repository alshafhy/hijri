<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportDataBuilder;
use App\Services\Valuation\ValuationReportPdfGenerator;
use App\Support\Valuation\Report\ComparablesPresenter;
use App\Support\Valuation\Report\PageBreaks;
use App\Support\Valuation\Report\ReportLabels;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
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

function htmlBetween(string $html, string $from, string $to): string
{
    $start = strpos($html, $from);
    if ($start === false) {
        return '';
    }
    $end = strpos($html, $to, $start + strlen($from));

    return substr($html, $start, ($end === false ? strlen($html) : $end) - $start);
}

function certificateCompany(string $html): string
{
    return htmlBetween($html, 'class="rpt-certificate-company"', '</p>');
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
        ->and(certificateCompany($html))->toContain(__('pdf.membership_branch').'</strong>: عقار')
        ->and(certificateCompany($html))->toContain(__('pdf.membership_category').'</strong>: أساسي')
        ->and(certificateCompany($html))->toContain(__('pdf.company_membership_number').'</strong>: 1110000001')
        ->and(certificateCompany($html))->toContain(__('pdf.company_reg_number').'</strong>: 1010000001')
        ->and($html)->not->toContain(__('pdf.branch_and_category'));
});

it('omits the certificate company rows whose values are empty', function () {
    $request = certificateFixture();
    $request->company?->update(['address' => null, 'company_membership_number' => null, 'company_reg_number' => '', 'membership_date' => null]);
    $request->evaluator?->update(['membership_branch_type' => null]);

    $html = renderFullReport(request: $request->refresh());
    $company = certificateCompany($html);

    expect($company)->toContain('شركة التقييم للاختبار')
        ->and($company)->toContain(__('pdf.evaluation_and_studies_department').'</strong>')
        ->and($company)->toContain(__('pdf.membership_category').'</strong>: أساسي')
        ->and($company)->toContain(__('pdf.end_date').'</strong>: 2027-09-15')
        ->and($company)->not->toContain(__('pdf.membership_saudi_authority'))
        ->and($company)->not->toContain(__('pdf.company_membership_number'))
        ->and($company)->not->toContain(__('pdf.company_reg_number'))
        ->and($company)->not->toContain(__('pdf.membership_branch').'</strong>');
});

it('leaves no dangling separators in the page footer when company values are empty', function () {
    $request = reportFixture();
    $request->company?->update(['address' => null, 'company_membership_number' => null, 'company_reg_number' => null]);

    $footer = htmlBetween(renderFullReport(request: $request->refresh()), 'class="rpt-footer-text"', '</td>');
    $text = trim((string) preg_replace('/\s+/u', ' ', strip_tags('<td '.$footer)));

    expect($text)->toBe('شركة التقييم للاختبار - '.__('pdf.Evaluation and Studies Department'));
});

it('renders the scope of work card', function () {
    $scope = htmlBetween(renderFullReport(), 'rpt-scope"', '</table>');

    expect($scope)->toContain(__('pdf.work_scope'))
        ->and($scope)->toContain(e(__('pdf.restrictions_on_use_des')))
        ->and($scope)->toContain(e(__('pdf.data_available_in_instrument')))
        ->and($scope)->toContain(__('pdf.التقرير الكامل'))
        ->and($scope)->toContain('('.app(ReportLabels::class)->option('base_value', 1).')')
        ->and($scope)->toContain(e(__('pdf.liquidation_value_des')))
        ->and($scope)->toContain('منصة السوق')
        ->and($scope)->toContain('سكني - فيلا')
        ->and($scope)->toContain(e(__('pdf.internal_evaluation')))
        ->and($scope)->toContain(e(__('pdf.seg_considered')))
        ->and($scope)->toContain(e(__('pdf.resident_independence_details')))
        ->and($scope)->toContain(e(__('pdf.disclaimer_des')))
        ->and($scope)->toContain(__('pdf.use_nature'))
        ->and($scope)->toContain('class="rpt-scope-deposit">DEP-1')
        ->and($scope)->not->toContain(__('pdf.conclusion_value'))
        ->and($scope)->not->toContain(__('pdf.important_assumptions'));
});

it('renders the valuation framework card and the nine standards', function () {
    $html = renderFullReport();
    $framework = htmlBetween($html, 'rpt-framework"', '</table>');
    $standards = htmlBetween($html, 'rpt-standards"', '</table>');

    expect($framework)->toContain(__('pdf.evaluation_framework'))
        ->and($framework)->toContain(__('pdf.ethics'))
        ->and($framework)->toContain(e(__('pdf.privacy_des')))
        ->and($framework)->toContain(__('pdf.competence'))
        ->and($framework)->toContain(e(__('pdf.professional_development_des')))
        ->and($framework)->toContain(__('pdf.quality'))
        ->and($framework)->toContain(e(__('pdf.continuous_improvement_des')))
        ->and($standards)->toContain(__('pdf.valuation_standards'));

    expect(config('report_branding.standards'))->toHaveCount(9);

    foreach (config('report_branding.standards') as $standard) {
        expect($standards)->toContain(e(__("pdf.standard_{$standard}")))
            ->and($standards)->toContain(e(__("pdf.standard_{$standard}_des")));
    }
});

it('shows the standards page only in the arabic report', function () {
    $builder = app(ValuationReportDataBuilder::class);
    $request = reportFixture();

    expect($builder->build($request, ReportExportVariant::Full)['framework']['show_standards'])->toBeTrue()
        ->and($builder->build($request, ReportExportVariant::FullEnglish)['framework']['show_standards'])->toBeFalse()
        ->and(renderFullReport(ReportExportVariant::FullEnglish, $request))->not->toContain('rpt-standards"')
        ->and(renderFullReport(ReportExportVariant::FullEnglish, $request))->toContain('rpt-framework"');
});

it('orders the summary, scope, framework and standards before the closing page', function () {
    $html = renderFullReport();

    $positions = array_map(fn (string $marker): int|false => strpos($html, $marker), [
        'rpt-executive-summary"', 'rpt-scope"', 'rpt-framework"', 'rpt-standards"', 'class="rpt-closing-page"',
    ]);
    $sorted = $positions;
    sort($sorted);

    expect($positions)->not->toContain(false)->and($positions)->toBe($sorted);
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

/**
 * @param  array<string, mixed>  $overrides
 */
function fullReportData(?ValuationRequest $request = null, array $overrides = []): array
{
    $data = $overrides + app(ValuationReportDataBuilder::class)->build($request ?? reportFixture(), ReportExportVariant::Full);

    return ['breaks' => (new PageBreaks)->build($data)] + $data;
}

function basicDataRows(ValuationRequest $request): array
{
    $data = app(ValuationReportDataBuilder::class)->build($request->refresh(), ReportExportVariant::Full);

    return array_column($data['basic_data']['rows'], 'value', 'label');
}

it('builds the basic evaluation data rows', function () {
    $request = reportFixture();
    $request->property?->update(['issued_by' => 'كتابة عدل الرياض', 'license_no' => 'L-55', 'license_date' => '1444/05/05']);

    expect(basicDataRows($request))->toBe([
        __('pdf.request_number') => 'R-REPORT-1',
        __('pdf.date') => '1445/01/01',
        __('pdf.instrument_number') => '310101010101',
        __('pdf.issued_by') => 'كتابة عدل الرياض',
        __('pdf.license_number') => 'L-55',
        __('pdf.license_date') => '1444/05/05',
    ]);
});

it('falls back through the instrument dates to the instrument text and a dash', function () {
    $request = reportFixture();
    $request->update(['number' => null]);
    $property = $request->property;

    $property?->update(['instrument_date' => null, 'instrument_gregorian_date' => '2023-07-19']);
    $gregorian = basicDataRows($request);

    $property?->update(['instrument_gregorian_date' => ' ', 'according_instrument' => true]);
    $according = basicDataRows($request);

    $property?->update(['according_instrument' => false, 'instrument_no' => null]);
    $dash = basicDataRows($request);

    expect($gregorian[__('pdf.date')])->toBe('2023-07-19')
        ->and($gregorian[__('pdf.request_number')])->toBe('77001')
        ->and($according[__('pdf.date')])->toBe(__('pdf.according_to_instrument'))
        ->and($dash[__('pdf.date')])->toBe('—')
        ->and($dash[__('pdf.instrument_number')])->toBe('—')
        ->and($dash[__('pdf.issued_by')])->toBe('—')
        ->and($dash)->not->toHaveKey(__('pdf.license_number'));
});

it('renders the basic data card and the property information cards', function () {
    $html = renderFullReport();
    $basic = htmlBetween($html, 'rpt-basic-data"', '</table>');
    $cards = htmlBetween($html, 'class="rpt-property-cards"', 'class="rpt-closing-page"');

    expect($basic)->toContain(__('pdf.basic_evaluation_data'))
        ->and($basic)->toContain('R-REPORT-1')
        ->and($basic)->toContain('1445/01/01')
        ->and($cards)->toContain(__('pdf.basic_property_data'))
        ->and($cards)->toContain(__('pdf.property_borders'))
        ->and($cards)->toContain('25 '.__('pdf.meter'))
        ->and($cards)->toContain(__('pdf.description_finishes_and_construction'))
        ->and((string) preg_replace('/>\s+</', '><', htmlBetween($cards, __('pdf.Details related to the property'), '</table>')))
        ->toContain('<td class="rpt-card-pair-label">'.__('pdf.best_use_current').'</td><td class="rpt-card-pair-value">نعم</td>')
        ->and($cards)->toContain(__('pdf.inspection_type').'</td>')
        ->and($cards)->toContain('<td colspan="3">'.__('pdf.inspection_inside_outside').'</td>')
        ->and(substr_count($cards, '<table class="rpt-card'))->toBe(6);
});

it('omits the finishes card for a land property', function () {
    $request = reportFixture();
    $request->property?->update(['property_kind' => 'أرض']);
    $cards = htmlBetween(renderFullReport(request: $request->refresh()), 'class="rpt-property-cards"', 'class="rpt-closing-page"');

    expect($cards)->toContain(__('pdf.property_borders'))
        ->and($cards)->not->toContain(__('pdf.description_finishes_and_construction'))
        ->and($cards)->not->toContain(__('pdf.best_use_current'));
});

it('renders the location maps after the property cards', function () {
    $map = 'data:image/png;base64,c3RyZWV0cw==';
    $html = view('pdf.report.full', fullReportData(overrides: ['location_maps' => ['streets' => $map, 'aerial' => $map]]))->render();
    $block = htmlBetween($html, 'class="rpt-location-maps"', 'class="rpt-closing-page"');

    expect($block)->toContain(__('pdf.location_on_map'))
        ->and($block)->toContain(__('pdf.location_of_property'))
        ->and($block)->toContain(__('pdf.picture_from_above'))
        ->and($block)->toContain('24.7136')
        ->and($block)->toContain('46.6753')
        ->and(substr_count($block, 'src="'.$map.'"'))->toBe(2)
        ->and(strpos($html, 'class="rpt-location-maps"'))->toBeGreaterThan((int) strpos($html, 'class="rpt-property-cards"'))
        ->and(strpos($html, 'rpt-basic-data"'))->toBeGreaterThan((int) strpos($html, 'rpt-framework"'));
});

it('skips the location maps block without map images', function () {
    $html = view('pdf.report.full', fullReportData(overrides: ['location_maps' => []]))->render();

    expect($html)->not->toContain('class="rpt-location-maps"');
});

it('computes the page breaks from the sections that precede each one', function () {
    config(['report_branding.assets.static_pages.ar' => []]);

    $request = reportFixture();
    $breaks = fullReportData($request)['breaks'];
    $request->property?->update(['print_valuation_certificate' => true]);
    $withCertificate = fullReportData($request->refresh())['breaks'];

    expect($breaks)->toMatchArray([
        'certificate' => false, 'static_pages' => false, 'summary' => false, 'scope' => true,
        'framework' => true, 'basic_data' => true, 'property_cards' => false, 'location_maps' => true,
    ])->and($withCertificate['certificate'])->toBeFalse()
        ->and($withCertificate['summary'])->toBeTrue();
});

it('never starts the main pages with an empty page', function (bool $certificate, bool $staticPages) {
    if (! $staticPages) {
        config(['report_branding.assets.static_pages.ar' => []]);
    }
    $request = $certificate ? certificateFixture() : reportFixture();
    $map = 'data:image/png;base64,c3RyZWV0cw==';

    $html = view('pdf.report.full', fullReportData($request, ['location_maps' => ['streets' => $map]]))->render();
    $main = substr(htmlBetween($html, 'class="rpt-main">', 'class="rpt-closing-page"'), strlen('class="rpt-main">'));

    expect(ltrim($main))->not->toStartWith('<pagebreak')
        ->and(preg_match('/<pagebreak[^>]*>\s*<pagebreak/', $html))->toBe(0);
})->with([
    'no certificate, no static pages' => [false, false],
    'no certificate, static pages' => [false, true],
    'certificate, no static pages' => [true, false],
    'certificate and static pages' => [true, true],
]);

it('does not open the framework with a page break without a property', function () {
    $request = reportFixture();
    $request->property?->delete();
    $html = renderFullReport(request: $request->refresh());
    $main = substr(htmlBetween($html, 'class="rpt-main">', 'class="rpt-closing-page"'), strlen('class="rpt-main">'));

    expect(ltrim($main))->not->toStartWith('<pagebreak')
        ->and($html)->not->toContain('rpt-basic-data');
});

it('prints two label/value pairs per line on the legacy 4-column cards', function () {
    $html = renderFullReport();
    $basic = htmlBetween($html, 'rpt-basic-data"', '</table>');
    $borders = htmlBetween($html, __('pdf.property_borders'), '</table>');
    $pair = fn (string $label, string $value): string => '<td class="rpt-card-pair-label">'.$label.'</td>'
        .'<td class="rpt-card-pair-value">'.$value.'</td>';
    $line = fn (string $html): string => (string) preg_replace('/>\s+</', '><', $html);

    expect($html)->toContain('<table class="rpt-card rpt-card-paired rpt-basic-data">')
        ->and($basic)->toContain('<th colspan="4">')
        ->and($line($basic))->toContain($pair(__('pdf.request_number'), 'R-REPORT-1').$pair(__('pdf.date'), '1445/01/01'))
        ->and($line($borders))->toContain($pair(__('pdf.north'), 'شارع عرض 20م').$pair(__('pdf.length'), '25 '.__('pdf.meter')))
        ->and($line($borders))->toContain('<td class="rpt-card-pair-label">'.__('pdf.total_land_area').'</td><td colspan="3">500 '.__('pdf.square_meter_text').'</td>');
});

it('keeps one label/value pair per line on 2-column cards', function () {
    $html = view('pdf.report.partials.card', ['title' => 'عنوان', 'rows' => [['label' => 'أ', 'value' => '1'], ['label' => 'ب', 'value' => '2']]])->render();

    expect($html)->toContain('<table class="rpt-card">')
        ->and($html)->toContain('<th colspan="2">عنوان</th>')
        ->and(substr_count($html, '<td class="rpt-card-label">'))->toBe(2)
        ->and($html)->not->toContain('rpt-card-pair');
});

it('does not open the scope with a blank page without certificate, static pages or summary', function () {
    config(['report_branding.assets.static_pages.ar' => []]);
    $html = view('pdf.report.full', fullReportData(overrides: ['summary' => null]))->render();
    $main = substr(htmlBetween($html, 'class="rpt-main">', 'class="rpt-closing-page"'), strlen('class="rpt-main">'));

    expect(ltrim($main))->toStartWith('<table class="rpt-card rpt-scope">')
        ->and(preg_match('/<pagebreak[^>]*>\s*<pagebreak/', $html))->toBe(0);
});

it('pairs each border side with its own length when a side name is empty', function () {
    $request = reportFixture();
    $request->property?->borders()->update(['north' => ' ', 'north_length' => '25', 'south' => 'جار', 'south_length' => '30']);
    $borders = htmlBetween(renderFullReport(request: $request->refresh()), __('pdf.property_borders'), '</table>');
    $pair = fn (string $label, string $value): string => '<td class="rpt-card-pair-label">'.$label.'</td>'
        .'<td class="rpt-card-pair-value">'.$value.'</td>';
    $line = (string) preg_replace('/>\s+</', '><', $borders);

    expect($line)->toContain($pair(__('pdf.north'), '—').$pair(__('pdf.length'), '25 '.__('pdf.meter')))
        ->and($line)->toContain($pair(__('pdf.south'), 'جار').$pair(__('pdf.length'), '30 '.__('pdf.meter')))
        ->and($line)->toContain($pair(__('pdf.east'), 'جار').$pair(__('pdf.length'), '20 '.__('pdf.meter')));
});

/**
 * Renders the full report with OpenStreetMap tiles faked, deleting only the map cache files it created.
 */
function renderFullReportWithMapTiles(ValuationRequest $request): string
{
    $files = fn (): array => glob(storage_path('app/private/report-map-cache/*.png')) ?: [];
    $before = $files();
    $image = imagecreatetruecolor(256, 256);
    ob_start();
    imagepng($image);
    $tile = (string) ob_get_clean();
    Http::swap(new Factory);
    Http::fake(['tile.openstreetmap.org/*' => Http::response($tile), '*' => Http::response('', 503)]);

    try {
        return renderFullReport(request: $request);
    } finally {
        array_map('unlink', array_diff($files(), $before));
    }
}

it('renders the comparables, their map and the adjustments matrix with the meter price rows', function () {
    $html = renderFullReportWithMapTiles(reportFixture());
    $block = htmlBetween($html, 'class="rpt-market-approach"', '<table class="rpt-data-table rpt-cost-table"');
    $comparables = (string) preg_replace('/>\s+</', '><', htmlBetween($block, 'rpt-comparables"', '</table>'));
    $matrix = (string) preg_replace('/>\s+</', '><', htmlBetween($block, 'rpt-adjustments', '</table>'));

    expect($comparables)->toContain(__('pdf.comparative_real_estate'))
        ->and($comparables)->toContain('<td class="rpt-data-label">'.__('pdf.comparative').' 1</td><td>أرض</td><td>شمالية</td><td>500</td><td>3,000.00</td><td>1,500,000.00</td>')
        ->and(substr_count($comparables, '<td class="rpt-data-label">'.__('pdf.comparative').' '))->toBe(3)
        ->and($comparables)->toContain('href="https://www.google.com/maps/@24.711,46.671,17z"')
        ->and($comparables)->toContain('منصة السوق')
        ->and($block)->toContain(__('pdf.comparison_map'))
        ->and($block)->toContain('<img class="rpt-comparables-map-image" src="data:image/png;base64,')
        ->and(substr_count($matrix, 'class="rpt-adjustments-comparator"'))->toBe(3)
        ->and($matrix)->toContain(__('pdf.The third comparator'))
        ->and($matrix)->not->toContain(__('pdf.The fourth comparator'))
        ->and($matrix)->toContain('<td class="rpt-data-label">'.__('pdf.Total settlement percentages').'</td><td colspan="2">5%</td><td colspan="2">3%</td><td colspan="2">0%</td>')
        ->and($matrix)->toContain('<td class="rpt-data-label">'.__('pdf.market conditions value').'</td><td></td><td>5%</td>')
        ->and($matrix)->toContain('<td class="rpt-data-label">'.__('pdf.proportional adjustment').'</td><td colspan="2">40%</td><td colspan="2">30%</td><td colspan="2">30%</td>')
        ->and(rtrim($matrix))->toEndWith('<tr class="rpt-highlight"><td class="rpt-data-label">'.__('pdf.meter price').'</td><td colspan="6">3,050.00</td></tr>'
            .'<tr class="rpt-highlight"><td class="rpt-data-label">'.__('pdf.meter price approximately').'</td><td colspan="6">3,000.00</td></tr></tbody>')
        ->and($block)->not->toContain('rpt-settlement')
        ->and(substr_count($block, '<table class="rpt-data-table'))->toBe(2)
        ->and(strpos($html, 'class="rpt-market-approach"'))->toBeGreaterThan((int) strpos($html, 'class="rpt-property-cards"'));
});

it('skips the whole comparables block when the comparisons are hidden or there are no comparables', function () {
    $request = reportFixture();
    $request->property?->total?->update(['hide_comparisons_info_table' => true]);
    $hidden = renderFullReport(request: $request->refresh());
    $request->property?->total?->update(['hide_comparisons_info_table' => false]);
    $request->property?->comparables()->delete();
    $empty = renderFullReport(request: $request->refresh());

    expect($hidden)->not->toContain('rpt-market-approach')
        ->and($hidden)->not->toContain(__('pdf.Settlements table'))
        ->and($empty)->not->toContain('rpt-market-approach')
        ->and(fullReportData($request)['market_approach'])->toBeNull();
});

it('opens the comparables block on a new page', function () {
    $data = fullReportData();
    $breaks = $data['breaks'];
    $main = (string) preg_replace('/>\s+</', '><', view('pdf.report.full', $data)->render());

    expect($breaks['market_approach'])->toBeTrue()
        ->and($main)->toContain('<pagebreak /><div class="rpt-market-approach">');
});

it('keeps value and ratio cells on criterion rows even without ratios', function () {
    $section = app(ValuationReportDataBuilder::class)->build(reportFixture(), ReportExportVariant::Full)['comparables_section'];
    foreach ($section['adjustments']['rows'] as $index => $row) {
        if ($row['key'] === 'area') {
            $section['adjustments']['rows'][$index]['percentages'] = [null, null, null];
        }
    }
    $presented = (new ComparablesPresenter)->build($section);
    $matrix = (string) preg_replace('/>\s+</', '><', view('pdf.report.partials.adjustments', ['market_approach' => $presented])->render());
    $rows = array_column((array) $presented['rows'], 'merged', 'key');

    expect($matrix)->toContain('<td class="rpt-data-label">'.__('pdf.space').'</td><td>500.00</td><td></td><td>600.00</td><td></td>')
        ->and($rows['area'])->toBeFalse()
        ->and($rows['proportional_adjustment'])->toBeTrue();
});

it('prints a comparable row only when the comparable has a type', function () {
    $request = reportFixture();
    $request->property?->comparables()->where('sequence', 2)->update(['real_estate_type' => null]);
    $comparables = htmlBetween(renderFullReport(request: $request->refresh()), 'rpt-comparables"', '</table>');

    expect(substr_count($comparables, '<td class="rpt-data-label">'.__('pdf.comparative').' '))->toBe(2)
        ->and($comparables)->not->toContain('>'.__('pdf.comparative').' 2<')
        ->and($comparables)->toContain(__('pdf.comparative_coordinates').' 2');
});

function compactHtml(string $html): string
{
    return (string) preg_replace('/>\s+</', '><', $html);
}

/**
 * @param  list<string>  $cells  label first, then the value cells
 */
function dataRow(array $cells): string
{
    $label = array_shift($cells);

    return '<td class="rpt-data-label">'.$label.'</td><td>'.implode('</td><td>', $cells).'</td>';
}

it('renders the cost table with the grid titles, the legacy extra rows and the estimated cost', function () {
    $data = fullReportData();
    $cost = compactHtml(htmlBetween(view('pdf.report.full', $data)->render(), 'rpt-cost-table"', '</table>'));
    $words = $data['cost']['final_in_words'];

    expect($cost)->toContain(__('pdf.property_evaluation'))
        ->and($cost)->toContain('<td class="rpt-cost-usage">'.__('pdf.usage').'</td><td>'.__('pdf.space').'</td><td>'.__('pdf.meter_price_riyals').'</td><td>'.__('pdf.total_price_riyals').'</td>')
        ->and($cost)->toContain(dataRow(['مساحة الأرض', '500', '3,000.00', '1,500,000.00']))
        ->and($cost)->toContain(dataRow(['الملحق الأرضي', '300', '1,500.00', '450,000.00']))
        ->and($cost)->toContain(dataRow(['الملحق العلوي', '150', '1,200.00', '180,000.00']))
        ->and($cost)->not->toContain('land_area')
        ->and($cost)->toContain(dataRow([__('pdf.developer_earnings').' (+)', '-', '-', '63,000.00']))
        ->and($cost)->toContain(dataRow([__('pdf.depreciation_amount').' (-)', '-', '-', '138,600.00']))
        ->and($cost)->toContain(dataRow([__('pdf.forced_sale').' (15%) (-)', '-', '%15', '300,000.00']))
        ->and($cost)->not->toContain(__('pdf.movables'))
        ->and($words)->not->toBe('')
        ->and($cost)->toContain('<td class="rpt-data-label">'.__('pdf.estimated_cost').'</td><td colspan="2">'.$words.'</td><td class="rpt-cost-amount">2,000,000.00</td>');
});

it('hides the zero cost rows and the forced sale outside the forced sale assumption like legacy', function () {
    $request = reportFixture();
    $request->property?->update(['value_assumption' => 1]);
    $request->property?->total?->update(['profit_amount' => 0, 'depreciation_amount' => null, 'movables' => 2500]);
    $cost = compactHtml(htmlBetween(renderFullReport(request: $request->refresh()), 'rpt-cost-table"', '</table>'));

    expect($cost)->not->toContain(__('pdf.developer_earnings'))
        ->and($cost)->not->toContain(__('pdf.depreciation_amount'))
        ->and($cost)->not->toContain(__('pdf.forced_sale'))
        ->and($cost)->toContain(dataRow([__('pdf.movables'), '2,500.00', '-', '2,500.00']));
});

it('renders the custom table with its title and every row padded to the widest row', function () {
    $table = compactHtml(htmlBetween(renderFullReport(), 'rpt-custom-table"', '</table>'));

    expect($table)->toContain('<th colspan="3">جدول الدخل</th>')
        ->and($table)->toContain('<td class="rpt-custom-field">'.__('pdf.field_name').'</td><td>'.__('pdf.the value').'</td><td>'.__('pdf.the value').'</td>')
        ->and($table)->toContain(dataRow(['الإيجار السنوي', '120000', '5%']))
        ->and($table)->toContain(dataRow(['المصاريف', '10000', '']))
        ->and(substr_count($table, '<td class="rpt-data-label">'))->toBe(2);
});

it('renders the final value with the amount and the value in words', function () {
    $data = fullReportData();
    $final = compactHtml(htmlBetween(view('pdf.report.full', $data)->render(), 'rpt-final-value"', '</table>'));
    $words = $data['cost']['final_in_words'];

    expect($final)->toContain('<th colspan="2">'.__('pdf.final_valuation_price').'</th>')
        ->and($final)->toContain('<td class="rpt-final-amount">2,000,000.00 '.__('pdf.saudi_riyal').'</td><td>'.$words.' '.__('pdf.only').'</td>');
});

it('renders the coordinator and evaluator notes', function () {
    $request = reportFixture();
    $request->property?->update(['notes_real_estate' => ' ملاحظة المنسق ', 'notes_property' => 'ملاحظة المقيم']);
    $notes = compactHtml(htmlBetween(renderFullReport(request: $request->refresh()), 'rpt-notes"', '</table>'));

    expect($notes)->toContain(__('pdf.opinion_of_appraisal_company'))
        ->and($notes)->toContain('<td class="rpt-data-label">'.__('pdf.moderator_notes').'</td><td class="rpt-notes-text">ملاحظة المنسق</td>')
        ->and($notes)->toContain('<td class="rpt-data-label">'.__('pdf.evaluator_notes').'</td><td class="rpt-notes-text">ملاحظة المقيم</td>');
});

it('renders the four signature columns with the signatures, the company stamp and the accreditation date', function () {
    Storage::fake('public');
    $request = reportFixture();
    $request->company?->update(['stamp_path' => UploadedFile::fake()->image('stamp.png', 100, 100)->store('company/stamp', 'public')]);
    $request->update(['ended_at' => '2026-10-15 10:00:00']);
    $html = renderFullReport(request: $request->refresh());
    $block = compactHtml(htmlBetween($html, 'rpt-signatures"', '</table>'));

    expect($block)->toContain('<th colspan="4">'.__('pdf.signatures').'</th>')
        ->and($block)->toContain('<td>'.__('pdf.resident_name').'</td><td>'.__('pdf.data_validator').'</td><td>'.__('pdf.report_approved').'</td><td>'.__('pdf.company_stamp').'</td>')
        ->and($block)->toContain('<div>مقيم الاختبار</div><div>'.__('pdf.membership_number_label').': - 1210000001</div><div>عقار - أساسي</div>')
        ->and($block)->toContain('<div>منسق الاختبار</div><img')
        ->and($block)->not->toContain('1210000002')
        ->and($block)->toContain('<div>معتمد الاختبار</div><div>'.__('pdf.membership_number_label').': - 1210000003</div><div>زميل</div>')
        ->and(substr_count($block, '<img class="rpt-signature-image" src="data:image/png;base64,'))->toBe(3)
        ->and(substr_count($block, '<img class="rpt-stamp-image" src="data:image/png;base64,'))->toBe(1)
        ->and($html)->toContain('<p class="rpt-accreditation-date">'.__('pdf.accreditation_date').': 2026/10/15</p>');
});

it('prints the name and membership without a broken image when a signature is missing', function () {
    $request = reportFixture();
    $request->evaluator?->update(['signature_path' => null]);
    $block = compactHtml(htmlBetween(renderFullReport(request: $request->refresh()), 'rpt-signatures"', '</table>'));

    expect($block)->toContain('<div>مقيم الاختبار</div><div>'.__('pdf.membership_number_label').': - 1210000001</div><div>عقار - أساسي</div></td>')
        ->and(substr_count($block, '<img class="rpt-signature-image"'))->toBe(2)
        ->and(substr_count($block, '<img'))->toBe(2)
        ->and($block)->not->toContain('<p class="rpt-accreditation-date">');
});

it('leaves the signatures block out of the draft but keeps the visit confirmation', function () {
    $html = renderFullReport(ReportExportVariant::FullDraft);

    expect($html)->not->toContain('rpt-signatures')
        ->and($html)->not->toContain(__('pdf.resident_name'))
        ->and($html)->toContain('<p class="rpt-visit-confirmation">'.__('pdf.visit_confirmation').'</p>');
});

it('prints the valuation sections in legacy order after the market approach', function () {
    $html = renderFullReport();
    $positions = array_map(fn (string $marker): int => (int) strpos($html, $marker), [
        'class="rpt-market-approach"', 'rpt-cost-table"', 'rpt-custom-table"', 'rpt-final-value"',
        'rpt-notes"', 'rpt-signatures"', 'class="rpt-visit-confirmation"', 'class="rpt-closing-page"',
    ]);
    $sorted = $positions;
    sort($sorted);

    expect($positions)->each->toBeGreaterThan(0)
        ->and($positions)->toBe($sorted);
});

it('lets the valuation sections flow after the market approach without blank pages', function () {
    $data = fullReportData();
    $html = view('pdf.report.full', $data)->render();

    expect($data['breaks'])->toMatchArray([
        'cost_table' => false, 'custom_tables' => false, 'final_value' => false, 'notes' => false, 'signature_block' => false,
    ])->and(preg_match('/<pagebreak[^>]*>\s*<pagebreak/', $html))->toBe(0);
});

it('skips the cost table, the final value and the notes without a property', function () {
    $request = reportFixture();
    $request->property?->delete();
    $html = renderFullReport(request: $request->refresh());

    expect($html)->not->toContain('rpt-cost-table')
        ->and($html)->not->toContain('rpt-final-value')
        ->and($html)->not->toContain('rpt-notes"')
        ->and($html)->toContain('rpt-signatures"');
});
