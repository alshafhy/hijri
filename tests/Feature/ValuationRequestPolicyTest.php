<?php

declare(strict_types=1);

use App\Policies\ValuationRequestPolicy;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Enums\UserStatus;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    foreach ([
        'valuation_request.view',
        'valuation_request.approve_final',
        'valuation_request.unapprove',
        'valuation_request.mark_evaluated',
        'valuation_request.qima_upload',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('manager', 'web')->givePermissionTo([
        'valuation_request.view',
        'valuation_request.approve_final',
        'valuation_request.unapprove',
        'valuation_request.mark_evaluated',
        'valuation_request.qima_upload',
    ]);

    Role::findOrCreate('evaluator', 'web')->givePermissionTo([
        'valuation_request.view',
        'valuation_request.mark_evaluated',
    ]);
});

it('allows only managers to unapprove', function () {
    $manager = User::factory()->create(['status' => UserStatus::Active]);
    $manager->assignRole('manager');

    $evaluator = User::factory()->create(['status' => UserStatus::Active]);
    $evaluator->assignRole('evaluator');

    $request = ValuationRequest::query()->create([
        'legacy_id' => 900001,
        'state' => 'approve',
        'approve' => 1,
        'evaluator_user_id' => $evaluator->id,
    ]);

    expect(Gate::forUser($manager)->allows('unapprove', $request))->toBeTrue()
        ->and(Gate::forUser($evaluator)->allows('unapprove', $request))->toBeFalse();
});

it('allows assigned evaluator to mark evaluated but not after qima lock', function () {
    $evaluator = User::factory()->create(['status' => UserStatus::Active]);
    $evaluator->assignRole('evaluator');

    $request = ValuationRequest::query()->create([
        'legacy_id' => 900002,
        'state' => 'waiting',
        'evaluator_user_id' => $evaluator->id,
    ]);

    expect(Gate::forUser($evaluator)->allows('markEvaluated', $request))->toBeTrue();

    $request->forceFill(['uploaded_on_qima' => true, 'qima_locked_at' => now()])->save();

    expect(Gate::forUser($evaluator)->allows('markEvaluated', $request))->toBeFalse();
});
