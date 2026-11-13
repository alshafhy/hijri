<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    foreach ([
        'dashboard.view',
        'valuation_request.view',
        'valuation_request.create',
        'valuation_request.view_logs',
        'user.view',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('manager', 'web')->syncPermissions(Permission::all());
    app()->setLocale('ar');
});

it('renders the dashboard with statistics and charts only', function () {
    $manager = User::factory()->create(['status' => UserStatus::Active]);
    $manager->assignRole('manager');

    ValuationRequest::query()->create([
        'number' => 'HOME-1',
        'reference' => 42,
        'state' => 'underEvaluative',
    ]);

    $html = $this->actingAs($manager)
        ->get(route('home'))
        ->assertOk()
        ->getContent();

    expect($html)
        ->toContain('لوحة التحكم')
        ->toContain('عدد التقييمات المعتمدة')
        ->toContain('dashboard-monthly-chart')
        ->toContain('dashboard-status-chart')
        ->and($html)->not->toContain('تقييم جديد')
        ->and($html)->not->toContain('لوحة التقييمات')
        ->and($html)->not->toContain('لوحة المستخدمين')
        ->and($html)->not->toContain('لوحة الأحداث')
        ->and($html)->not->toContain('أحدث التقييمات')
        ->and($html)->not->toContain('أحدث الأحداث')
        ->and($html)->not->toContain('اسم العميل')
        ->and($html)->not->toContain('>underEvaluative<');
});
