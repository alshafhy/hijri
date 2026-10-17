<?php

declare(strict_types=1);

it('ships the same report text keys in Arabic and English', function () {
    $ar = require resource_path('lang/ar/pdf.php');
    $en = require resource_path('lang/en/pdf.php');

    expect(array_values(array_diff(array_keys($ar), array_keys($en))))->toBe([])
        ->and(array_values(array_diff(array_keys($en), array_keys($ar))))->toBe([]);
});

it('does not hard-code another company name in the report texts', function () {
    $ar = implode(' ', require resource_path('lang/ar/pdf.php'));
    $en = implode(' ', require resource_path('lang/en/pdf.php'));

    expect($ar)->not->toContain('شركة ميزة')
        ->and($en)->not->toContain('Meeza')
        ->and(__('pdf.certification_and_disclaimer_des', ['company' => 'شركة الاختبار'], 'ar'))->toContain('شركة الاختبار')
        ->and(__('pdf.based_on_studies', ['company' => 'Test Co'], 'en'))->toContain('Test Co');
});

it('resolves the standards list used by the framework page', function (string $code, string $locale) {
    expect(__("pdf.standard_{$code}", [], $locale))->not->toBe("pdf.standard_{$code}")
        ->and(__("pdf.standard_{$code}_des", [], $locale))->not->toBe("pdf.standard_{$code}_des");
})->with(['100', '101', '102', '103', '104', '105', '106', '400', '410'])->with(['ar', 'en']);
