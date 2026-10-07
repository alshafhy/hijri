<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\UpdateUserData;
use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Utils\PermissionsUtil;
use Illuminate\Support\Facades\DB;

final class UpdateUserAction
{
    public function __invoke(User $user, UpdateUserData $data): User
    {
        return DB::transaction(function () use ($user, $data): User {
            $user->fill([
                'name' => $data->name,
                'username' => $data->username,
                'email' => $data->email,
                'branch_id' => $data->branchId,
            ]);
            $user->save();

            $user->syncRoles($data->roleIds);
            PermissionsUtil::clearPermissionCash();

            $fresh = $user->fresh(['roles', 'branch']) ?? $user;

            RefreshUserMenuCacheJob::dispatch($fresh->id);

            return $fresh;
        });
    }
}
