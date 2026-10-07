<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_login_returns_standard_envelope(): void
    {
        $this->seed(PermissionsSeeder::class);

        $user = User::factory()->create([
            'username' => 'apiadmin',
            'email' => 'apiadmin@example.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('super-admin');

        $response = $this->postJson('/api/v1/login', [
            'username' => 'apiadmin',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'data' => ['user', 'access_token', 'token_type'],
                'meta',
                'errors',
            ]);
    }

    public function test_api_users_index_is_paginated_and_authorized(): void
    {
        $this->seed(PermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/users')
            ->assertOk()
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
                'errors',
            ]);
    }

    public function test_creating_user_dispatches_menu_cache_job(): void
    {
        Queue::fake();

        $this->seed(BranchSeeder::class);
        $this->seed(PermissionsSeeder::class);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        Sanctum::actingAs($admin);

        $this->postJson('/api/v1/users', [
            'name' => 'New User',
            'username' => 'newuser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        Queue::assertPushed(RefreshUserMenuCacheJob::class);
    }
}
