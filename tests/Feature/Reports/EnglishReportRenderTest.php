<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportDataBuilder;
use App\Services\Valuation\ValuationReportPdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => Http::fake(['*' => Http::response('', 503)]));

it('renders the english report with english names, english cost rows and no standards page', function () {
    $request = englishFixture();
    app()->setLocale('ar');

    $prepared = app(ValuationReportPdfGenerator::class)->prepare($request, ReportExportVariant::FullEnglish);
    $html = $prepared['html'];
    $data = $prepared['data'];

    expect($html)->toContain('dir="ltr"')
        ->and($html)->toContain('Test Customer')
        ->and($html)->toContain('Test Owner')
        ->and($html)->toContain(trans('pdf.executive_summary', [], 'en'))
        ->and($html)->not->toContain(trans('pdf.executive_summary', [], 'ar'))
        ->and($html)->not->toContain(trans('pdf.standard_100', [], 'en'))
        ->and($html)->toContain('million')
        ->and(app()->getLocale())->toBe('ar');

    expect(array_column($data['summary']['continued'], 'value', 'label'))->toMatchArray([
        trans('pdf.customer_name', [], 'en') => 'Test Customer',
        trans('pdf.report_user_name', [], 'en') => 'Test Owner',
    ])
        ->and($data['cost']['show_land_rows'])->toBeTrue()
        ->and(array_slice(array_column($data['cost_table']['rows'], 'label'), 0, 3))->toBe([
            'Total Land Area', 'Ground Annex', 'Upper Annex',
        ])
        ->and($data['framework']['show_standards'])->toBeFalse()
        ->and($data['static_pages'])->toHaveCount(1);
});

it('keeps the arabic names and cost rows in the arabic report', function () {
    $request = englishFixture();

    $data = app(ValuationReportDataBuilder::class)->build($request, ReportExportVariant::Full);

    expect($data['cost']['show_land_rows'])->toBeFalse()
        ->and(array_slice(array_column($data['cost_table']['rows'], 'label'), 0, 3))->toBe([
            'مساحة الأرض', 'الملحق الأرضي', 'الملحق العلوي',
        ])
        ->and(array_column($data['summary']['continued'], 'value', 'label'))->toMatchArray([
            __('pdf.customer_name') => 'عميل الاختبار',
            __('pdf.report_user_name') => 'عميل الاختبار',
        ]);
});

it('falls back to the client and arabic names when the english names are empty', function () {
    $request = englishFixture(customerNameEn: null, ownerNameEn: null);
    app()->setLocale('en');

    $data = app(ValuationReportDataBuilder::class)->build($request->refresh(), ReportExportVariant::FullEnglish);

    expect(array_column($data['summary']['continued'], 'value', 'label'))->toMatchArray([
        trans('pdf.customer_name', [], 'en') => 'عميل الاختبار',
        trans('pdf.report_user_name', [], 'en') => 'مالك الاختبار',
    ]);
});

it('falls back to the client name before the arabic names', function () {
    $request = englishFixture(customerNameEn: null, ownerNameEn: null);
    $partner = Partner::query()->create(['legacy_id' => 990001, 'name' => 'شريك الاختبار']);
    PartnerContract::query()->create(['partner_id' => $partner->id, 'valuation_request_id' => $request->id]);
    app()->setLocale('en');

    $data = app(ValuationReportDataBuilder::class)->build($request->refresh(), ReportExportVariant::FullEnglish);

    expect(array_column($data['summary']['continued'], 'value', 'label'))->toMatchArray([
        trans('pdf.customer_name', [], 'en') => 'شريك الاختبار',
        trans('pdf.report_user_name', [], 'en') => 'شريك الاختبار',
    ]);
});

function englishFixture(?string $customerNameEn = 'Test Customer', ?string $ownerNameEn = 'Test Owner'): ValuationRequest
{
    $request = reportFixture();
    $request->property?->update([
        'customer_name_en' => $customerNameEn,
        'owner_name_en' => $ownerNameEn,
    ]);

    return $request->refresh();
}
