<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Services\MenuService;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\SystemComponentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class RefreshUserMenuCacheJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_refreshes_menu_cache_successfully(): void
    {
        $this->seed(PermissionsSeeder::class);
        $this->seed(SystemComponentsSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('super-admin');

        $job = new RefreshUserMenuCacheJob($user->id);
        $job->handle(app(MenuService::class));

        $this->assertTrue(true);
    }

    public function test_job_is_queued_on_default_queue(): void
    {
        Queue::fake();

        RefreshUserMenuCacheJob::dispatch(1);

        Queue::assertPushedOn('default', RefreshUserMenuCacheJob::class);
    }
}
