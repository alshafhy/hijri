<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DTOs\User\CreateUserData;
use App\Enums\UserStatus;
use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Utils\PermissionsUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

final class CreateUserAction
{
    public function __invoke(CreateUserData $data): User
    {
        return DB::transaction(function () use ($data): User {
            $plainPassword = $data->password
                ?? ($data->username.(string) config('users.default_password_suffix'));

            $user = User::query()->create([
                'name' => $data->name,
                'username' => $data->username,
                'email' => $data->email,
                'password' => Hash::make($plainPassword),
                'branch_id' => $data->branchId ?? (int) config('users.default_branch_id'),
                'pass_need_to_be_changed' => $data->password === null ? 1 : 0,
                'status' => UserStatus::Active->value,
            ]);

            if ($data->roleIds !== []) {
                $user->syncRoles($data->roleIds);
                PermissionsUtil::clearPermissionCash();
            }

            $fresh = $user->fresh(['roles', 'branch']) ?? $user;

            RefreshUserMenuCacheJob::dispatch($fresh->id);

            return $fresh;
        });
    }
}
