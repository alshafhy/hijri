<?php

declare(strict_types=1);

use App\Enums\Valuation\ReportExportVariant;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportDataBuilder;
use App\Support\HijriDate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(fn () => Http::fake(['*' => Http::response('', 503)]));

it('adds the report section keys for the full report', function () {
    $data = (new ValuationReportDataBuilder)->build(reportFixture(), ReportExportVariant::Full);

    expect($data['branding']['name'])->toBe('شركة التقييم للاختبار')
        ->and($data['signatures']['evaluator']['name'])->toBe('مقيم الاختبار')
        ->and($data['signatures']['approver']['name'])->toBe('معتمد الاختبار')
        ->and($data['cost']['rows'])->not->toBeEmpty()
        ->and($data['cost']['final_amount'])->toBe($data['final_amount_raw'])
        ->and($data['comparables_section']['comparables'])->toHaveCount(3)
        ->and($data['comparables_section']['adjustments']['columns'])->toBe([1, 2, 3])
        ->and($data['property_cards'])->not->toBeEmpty()
        ->and($data['property_cards'][0])->toHaveKeys(['title', 'rows'])
        ->and($data['standards'][0])->toHaveKeys(['title', 'rows'])
        ->and($data['custom_tables'][0]['title'])->toBe('جدول الدخل')
        ->and(array_column($data['attachment_images'], 'label'))->toBe(['صورة للعقار'])
        ->and($data['static_pages'])->toHaveCount(2)
        ->and($data['static_pages'][0])->toStartWith('data:image/png;base64,')
        ->and($data['license_image'])->toStartWith('data:image/jpeg;base64,')
        ->and($data['overlay_logo'])->toStartWith('data:image/png;base64,')
        ->and($data['show_signatures'])->toBeTrue()
        ->and($data['hijri_issue_date'])->toBe(HijriDate::fromGregorian(now())->numeric());
});

it('hides signatures on the draft report', function () {
    $data = (new ValuationReportDataBuilder)->build(reportFixture(), ReportExportVariant::FullDraft);

    expect($data['show_signatures'])->toBeFalse()
        ->and($data['signatures']['evaluator'])->not->toBeNull()
        ->and($data['watermark'])->toBe('مسودة');
});

it('only the draft variant hides signatures', function () {
    $hidden = array_filter(ReportExportVariant::cases(), fn (ReportExportVariant $v): bool => ! $v->showsSignatures());

    expect(array_values($hidden))->toBe([ReportExportVariant::FullDraft]);
});

it('embeds the licence and watermark images only for the full variants', function () {
    $builder = new ValuationReportDataBuilder;
    $request = reportFixture();

    expect($builder->build($request, ReportExportVariant::FullDraft)['license_image'])->toStartWith('data:image/jpeg;base64,')
        ->and($builder->build($request, ReportExportVariant::FullDraft)['overlay_logo'])->toStartWith('data:image/png;base64,')
        ->and($builder->build($request, ReportExportVariant::Enforcement)['license_image'])->toBeNull()
        ->and($builder->build($request, ReportExportVariant::Enforcement)['overlay_logo'])->toBeNull();
});

it('uses the english static pages for the english report', function () {
    app()->setLocale('en');
    $data = (new ValuationReportDataBuilder)->build(reportFixture(), ReportExportVariant::FullEnglish);

    expect($data['static_pages'])->toHaveCount(1);
});

it('returns safe empty section values for a request without property or company', function () {
    $request = ValuationRequest::query()->create(['number' => 'R-EMPTY-1']);

    $data = (new ValuationReportDataBuilder)->build($request, ReportExportVariant::Full);

    expect($data['branding'])->toBeNull()
        ->and($data['certificate'])->toBeNull()
        ->and($data['summary'])->toBeNull()
        ->and($data['scope'])->toBeNull()
        ->and($data['page_footer'])->toBeNull()
        ->and($data['signatures'])->toBe(['evaluator' => null, 'coordinator' => null, 'approver' => null])
        ->and($data['cost'])->toBeNull()
        ->and($data['comparables_section'])->toBeNull()
        ->and($data['property_cards'])->toBe([])
        ->and($data['standards'])->toBe([])
        ->and($data['custom_tables'])->toBe([])
        ->and($data['attachment_images'])->toBe([])
        ->and($data['static_pages'])->toHaveCount(2)
        ->and($data['show_signatures'])->toBeTrue()
        ->and($data['hijri_issue_date'])->toBeString();
});
