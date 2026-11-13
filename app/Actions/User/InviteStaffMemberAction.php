<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\UserStatus;
use App\Jobs\User\RefreshUserMenuCacheJob;
use App\Models\User;
use App\Utils\PermissionsUtil;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class InviteStaffMemberAction
{
    public function __construct(private readonly SendStaffInvitationAction $sendInvitation) {}

    /**
     * @param  array{role: string, name: string, email: string, phone_number?: ?string}  $data
     */
    public function execute(User $actor, array $data): User
    {
        Gate::forUser($actor)->authorize('create', User::class);

        if (self::staffCount() >= (int) config('users.staff_limit')) {
            throw ValidationException::withMessages(['email' => __('You have reached the maximum number of staff members.')]);
        }

        $member = DB::transaction(function () use ($actor, $data): User {
            $member = User::query()->create([
                'name' => $data['name'],
                'username' => $this->uniqueUsername($data['email']),
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'password' => Hash::make(Str::random(40)),
                'branch_id' => $actor->branch_id,
                'status' => UserStatus::Inactive->value,
            ]);
            $member->assignRole($data['role']);
            PermissionsUtil::clearPermissionCash();

            return $member;
        });

        $this->sendInvitation->execute($actor, $member);
        RefreshUserMenuCacheJob::dispatch($member->id);

        return $member;
    }

    public static function staffCount(): int
    {
        return User::role(config('users.staff_roles'))->count();
    }

    private function uniqueUsername(string $email): string
    {
        $base = Str::slug(Str::before($email, '@'), '.') ?: 'user';
        $username = $base;
        $suffix = 1;

        while (User::withTrashed()->where('username', $username)->exists()) {
            $username = $base.'.'.(++$suffix);
        }

        return $username;
    }
}
