<?php

declare(strict_types=1);

use App\Models\ValuationCustomTableRow;
use App\Support\Valuation\Report\CustomTableSection;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->setLocale('ar'));

it('pads ragged rows to the widest row of the table', function () {
    $tables = (new CustomTableSection)->build(reportFixture());

    expect($tables)->toBe([[
        'title' => 'جدول الدخل',
        'columns' => 2,
        'rows' => [
            ['label' => 'الإيجار السنوي', 'values' => ['120000', '5%']],
            ['label' => 'المصاريف', 'values' => ['10000', '']],
        ],
    ]]);
});

it('builds one table per title and falls back to the default title', function () {
    $request = reportFixture();
    ValuationCustomTableRow::query()->create([
        'valuation_request_id' => $request->id, 'table_title' => null,
        'label' => 'بند', 'values' => ['1', '2', '3'], 'sort_order' => 0,
    ]);

    $tables = (new CustomTableSection)->build($request->refresh());

    expect(array_column($tables, 'title'))->toBe(['جدول الدخل', __('pdf.custom_table')])
        ->and($tables[1]['columns'])->toBe(3)
        ->and($tables[1]['rows'])->toBe([['label' => 'بند', 'values' => ['1', '2', '3']]]);
});

it('orders rows by sort order then id', function () {
    $request = reportFixture();
    ValuationCustomTableRow::query()->create([
        'valuation_request_id' => $request->id, 'table_title' => 'جدول الدخل',
        'label' => 'أول', 'values' => ['0'], 'sort_order' => -1,
    ]);
    ValuationCustomTableRow::query()->create([
        'valuation_request_id' => $request->id, 'table_title' => 'جدول الدخل',
        'label' => 'مكرر', 'values' => ['0'], 'sort_order' => 1,
    ]);

    $tables = (new CustomTableSection)->build($request->refresh());

    expect(array_column($tables[0]['rows'], 'label'))->toBe(['أول', 'الإيجار السنوي', 'المصاريف', 'مكرر']);
});

it('returns no tables when the request has no custom rows', function () {
    $request = reportFixture();
    $request->customTableRows()->delete();

    expect((new CustomTableSection)->build($request->refresh()))->toBe([]);
});
