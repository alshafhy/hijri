<?php

declare(strict_types=1);

namespace App\Jobs\User;

use App\Models\User;
use App\Services\MenuService;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

final class RefreshUserMenuCacheJob implements ShouldBeUnique, ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public int $timeout = 60;

    public int $uniqueFor = 120;

    public function __construct(public readonly int $userId)
    {
        $this->onQueue('default');
    }

    public function uniqueId(): string
    {
        return 'refresh-user-menu-cache:'.$this->userId;
    }

    public function handle(MenuService $menuService): void
    {
        $user = User::query()->find($this->userId);

        if ($user === null) {
            return;
        }

        $menuService->clearMenuCache($user->id);
        $menuService->getMenuForUser($user);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('RefreshUserMenuCacheJob failed', [
            'user_id' => $this->userId,
            'message' => $exception?->getMessage(),
        ]);
    }
}
