<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_users_index(): void
    {
        $this->get(route('dashboard.users.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_admin_can_view_users_index(): void
    {
        $this->seed(PermissionsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $this->actingAs($user)
            ->get(route('dashboard.users.index'))
            ->assertOk();
    }
}
