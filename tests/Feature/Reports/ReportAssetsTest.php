<?php

declare(strict_types=1);

it('has every configured report asset on disk', function (string $key) {
    $path = config("report_branding.assets.{$key}");

    expect($path)->toBeString()
        ->and(is_file(public_path($path)))->toBeTrue();
})->with(['cover', 'closing', 'page_bg', 'license', 'overlay_logo', 'static_pages.ar.0', 'static_pages.ar.1', 'static_pages.en.0']);

it('exposes the report brand colours and standards list', function () {
    expect(config('report_branding.colors.primary'))->toBe('#F79C20')
        ->and(config('report_branding.standards'))->toBe(['100', '101', '102', '103', '104', '105', '106', '400', '410']);
});
