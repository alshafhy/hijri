<?php

declare(strict_types=1);

use App\Support\Valuation\Report\ReportLabels;

it('resolves option codes from constants', function () {
    $labels = new ReportLabels;
    $list = config('constants.select.base_value.options');

    expect($labels->option('base_value', 1))->toBe(__($list[1]))
        ->and($labels->option('base_value', '1'))->toBe(__($list[1]))
        ->and($labels->option('base_value', null))->toBeNull()
        ->and($labels->option('base_value', ''))->toBeNull()
        ->and($labels->option('base_value', 'unknown-code'))->toBe('unknown-code');
});

it('treats placeholder options as empty', function () {
    $labels = new ReportLabels;

    expect($labels->option('base_value', 0))->toBeNull()
        ->and($labels->option('base_value', '0'))->toBeNull()
        ->and($labels->option('market_valuation_way_sub', 0))->toBeNull();
});

it('keeps meaningful zero-coded options', function () {
    $list = config('constants.select.market_valuation_way_main.options');

    expect((new ReportLabels)->option('market_valuation_way_main', 0))->toBe(__($list[0]));
});

it('resolves string keyed and flat lists', function () {
    $labels = new ReportLabels;

    expect($labels->option('flooring_type', 'رخام'))->toBe(__('رخام'))
        ->and($labels->option('ownership_type', 2))->toBe(__(config('constants.select.ownership_type.2')));
});

it('returns the raw value for unknown lists', function () {
    $labels = new ReportLabels;

    expect($labels->option('no_such_list', 7))->toBe('7')
        ->and($labels->option('no_such_list', null))->toBeNull();
});

it('translates comma separated values', function () {
    $labels = new ReportLabels;

    expect($labels->csv('رخام, جرانيت ,'))->toBe('رخام، جرانيت')
        ->and($labels->csv('a,b', ' / '))->toBe('a / b')
        ->and($labels->csv(''))->toBeNull()
        ->and($labels->csv(' , '))->toBeNull()
        ->and($labels->csv(null))->toBeNull();
});
