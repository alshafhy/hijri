<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Overrides\Spatie\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.view');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.view');
    }

    public function create(User $user): bool
    {
        return $user->can('role.create');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.edit');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->can('role.delete');
    }
}
