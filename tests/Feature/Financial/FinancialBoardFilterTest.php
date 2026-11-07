<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    foreach (['dashboard.view', 'financial.view'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions(['dashboard.view', 'financial.view']);
    app()->setLocale('ar');

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
});

it('renders a wide month filter that does not wrap the filter label', function () {
    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.financial.index'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('name="month"')
        ->and($html)->toContain('type="month"')
        ->and($html)->toContain('dir="ltr"')
        ->and($html)->toContain('data-native')
        ->and($html)->toContain('text-nowrap')
        ->and($html)->toContain('تصفية')
        ->and($html)->toContain('id="financial-month"')
        ->and($html)->not->toContain('btn-sm btn-outline-primary');
});
