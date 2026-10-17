<?php

declare(strict_types=1);

use App\Models\Company;
use App\Support\Valuation\Report\BrandingSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('resolves company branding with the report logo falling back to the main logo', function () {
    Storage::fake('public');
    $company = Company::query()->create([
        'name' => 'شركة الاختبار',
        'main_logo_path' => UploadedFile::fake()->image('l.png')->store('company/1/logo', 'public'),
        'stamp_path' => UploadedFile::fake()->image('s.png')->store('company/1/stamp', 'public'),
    ]);

    $branding = (new BrandingSection)->build($company);

    expect($branding['logo'])->toStartWith('data:image/png')
        ->and($branding['report_logo'])->toBe($branding['logo'])
        ->and($branding['stamp'])->toStartWith('data:image/png')
        ->and($branding['name'])->toBe('شركة الاختبار');
});

it('exposes company text columns and null images when nothing is uploaded', function () {
    Storage::fake('public');
    $company = Company::query()->create([
        'name' => 'شركة',
        'address' => 'الرياض',
        'company_membership_number' => 'M-1',
        'company_reg_number' => 'R-2',
    ]);

    $branding = (new BrandingSection)->build($company);

    expect($branding['logo'])->toBeNull()
        ->and($branding['report_logo'])->toBeNull()
        ->and($branding['stamp'])->toBeNull()
        ->and($branding['address'])->toBe('الرياض')
        ->and($branding['membership_number'])->toBe('M-1')
        ->and($branding['reg_number'])->toBe('R-2')
        ->and($branding['membership_date'])->toBeNull()
        ->and($branding['accreditation_end_date'])->toBeNull();
});
