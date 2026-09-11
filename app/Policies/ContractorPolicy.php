<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contractor;
use App\Models\User;

class ContractorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contractor.view');
    }

    public function view(User $user, Contractor $contractor): bool
    {
        return $user->can('contractor.view');
    }

    public function create(User $user): bool
    {
        return $user->can('contractor.create');
    }

    public function update(User $user, Contractor $contractor): bool
    {
        return $user->can('contractor.edit');
    }

    public function delete(User $user, Contractor $contractor): bool
    {
        return $user->can('contractor.delete');
    }

    public function activate(User $user, Contractor $contractor): bool
    {
        return $user->can('contractor.activate');
    }
}
