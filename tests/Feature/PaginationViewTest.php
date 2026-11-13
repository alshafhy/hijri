<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    foreach (['dashboard.view', 'partner.view'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions(['dashboard.view', 'partner.view']);
    app()->setLocale('ar');

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');

    foreach (range(1, 26) as $i) {
        Partner::query()->create([
            'name' => "عميل صفحة {$i}",
            'state' => Partner::STATE_ACTIVE,
        ]);
    }
});

it('renders bootstrap page numbers instead of tailwind previous-next only', function () {
    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.partners.index'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('class="pagination"')
        ->and($html)->toContain('page-item')
        ->and($html)->toContain('page-link')
        ->and($html)->not->toContain('sm:hidden')
        ->and($html)->toContain('page-link">1</span>')
        ->and($html)->toContain('>2</a>');
});
