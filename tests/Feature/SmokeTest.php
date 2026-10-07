<?php

declare(strict_types=1);

use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Overrides\Spatie\Role;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

it('shows the login page', function (): void {
    $this->get(route('login'))->assertOk();
});

it('authenticates an admin with username and password', function (): void {
    $this->seed(PermissionsSeeder::class);

    $user = User::factory()->create([
        'username' => 'gate5admin',
        'password' => Hash::make('password'),
    ]);
    $user->assignRole('super-admin');

    $this->post('/login', [
        'username' => 'gate5admin',
        'password' => 'password',
    ])->assertRedirect(route('home'));

    $this->assertAuthenticatedAs($user);
});

it('allows admin to open users and roles index', function (): void {
    $this->seed(PermissionsSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('super-admin');

    $this->actingAs($user)
        ->get(route('dashboard.users.index'))
        ->assertOk();

    $this->actingAs($user)
        ->get(route('dashboard.roles.index'))
        ->assertOk();
});

it('denies limited users from users index', function (): void {
    $this->seed(PermissionsSeeder::class);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard.users.index'))
        ->assertForbidden();
});

it('assigns role permissions through the policy-backed flow', function (): void {
    $this->seed(PermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $role = Role::create([
        'name' => 'editor',
        'guard_name' => 'web',
        'ar_name' => 'محرر',
    ]);

    $permission = Permission::findByName('user.view', 'web');
    $role->givePermissionTo($permission);

    expect($role->hasPermissionTo('user.view'))->toBeTrue();
    expect($admin->can('user.view'))->toBeTrue();
});

it('queues refresh user menu cache jobs', function (): void {
    Queue::fake();

    RefreshUserMenuCacheJob::dispatch(99);

    Queue::assertPushedOn('default', RefreshUserMenuCacheJob::class);
});

it('creates a user through the action stack when seeded branches exist', function (): void {
    Queue::fake();
    $this->seed(BranchSeeder::class);
    $this->seed(PermissionsSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super-admin');

    $this->actingAs($admin)
        ->post(route('dashboard.users.store'), [
            'name' => 'Smoke User',
            'username' => 'smokeuser',
            'email' => 'smoke@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])
        ->assertRedirect(route('dashboard.users.index'));

    $this->assertDatabaseHas('users', ['username' => 'smokeuser']);
    Queue::assertPushed(RefreshUserMenuCacheJob::class);
});
