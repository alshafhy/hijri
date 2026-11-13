<?php

declare(strict_types=1);

use App\Models\User;
use App\Support\Valuation\Report\SignaturesSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('builds evaluator, coordinator and approver blocks with membership data and signatures', function () {
    $request = reportFixture();

    $signatures = (new SignaturesSection)->build($request);

    expect($signatures['evaluator'])->toMatchArray([
        'name' => 'مقيم الاختبار',
        'membership_number' => '1210000001',
        'membership_category' => 'أساسي',
        'membership_branch_type' => 'عقار',
    ])
        ->and($signatures['evaluator']['signature'])->toStartWith('data:image/png')
        ->and($signatures['coordinator']['name'])->toBe('منسق الاختبار')
        ->and($signatures['coordinator']['membership_category'])->toBeNull()
        ->and($signatures['approver']['name'])->toBe('معتمد الاختبار')
        ->and($signatures['approver']['membership_category'])->toBe('زميل');
});

it('takes the approver from the latest approval activity causer', function () {
    $request = reportFixture();
    $first = User::factory()->create(['name' => 'معتمد قديم']);
    $latest = User::factory()->create(['name' => 'معتمد نهائي']);
    activity('valuation')->performedOn($request)->causedBy($first)->event('approved')->log('Valuation finally approved');
    activity('valuation')->performedOn($request)->causedBy($latest)->event('approved')->log('Valuation finally approved');
    activity('valuation')->performedOn($request)->causedBy($first)->event('evaluated')->log('Evaluated');

    $signatures = (new SignaturesSection)->build($request);

    expect($signatures['approver']['name'])->toBe('معتمد نهائي')
        ->and($signatures['approver']['signature'])->toBeNull();
});

it('returns a null signature when the signature file is missing', function () {
    $request = reportFixture();
    Storage::disk('local')->delete((string) $request->evaluator?->signature_path);

    $signatures = (new SignaturesSection)->build($request->refresh());

    expect($signatures['evaluator']['signature'])->toBeNull()
        ->and($signatures['evaluator']['name'])->toBe('مقيم الاختبار');
});

it('returns null blocks when users are not assigned', function () {
    $request = reportFixture();
    $request->update(['evaluator_user_id' => null, 'sub_user_id' => null]);

    $signatures = (new SignaturesSection)->build($request->refresh());

    expect($signatures['evaluator'])->toBeNull()
        ->and($signatures['approver'])->toBeNull()
        ->and($signatures['coordinator'])->not->toBeNull();
});
