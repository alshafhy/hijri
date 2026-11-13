<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('super-admin', 'web');
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('evaluator', 'web');
});

it('lets a super-admin impersonate another user and then leave', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $target = User::factory()->create();
    $target->assignRole('evaluator');

    $this->actingAs($admin)
        ->get(route('impersonate', $target->id))
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($target);
    expect(session('impersonated_by'))->toBe($admin->id);

    $this->get(route('impersonate.leave'))
        ->assertRedirect('/');

    $this->assertAuthenticatedAs($admin);
    expect(session('impersonated_by'))->toBeNull();
});

it('forbids an evaluator from impersonating another user', function (): void {
    $evaluator = User::factory()->create();
    $evaluator->assignRole('evaluator');

    $target = User::factory()->create();

    $this->actingAs($evaluator)
        ->get(route('impersonate', $target->id))
        ->assertForbidden();

    $this->assertAuthenticatedAs($evaluator);
});

it('forbids impersonating a super-admin', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole('super-admin');

    $this->actingAs($admin)
        ->get(route('impersonate', $superAdmin->id))
        ->assertRedirect();

    $this->assertAuthenticatedAs($admin);
    expect(session('impersonated_by'))->toBeNull();
});
