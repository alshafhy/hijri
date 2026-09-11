<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;

class ContractPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('contract.view');
    }

    public function view(User $user, Contract $contract): bool
    {
        return $user->can('contract.view');
    }

    public function create(User $user): bool
    {
        return $user->can('contract.create');
    }

    public function markPaid(User $user, Contract $contract): bool
    {
        return $user->can('contract.mark_paid');
    }

    public function delete(User $user, Contract $contract): bool
    {
        return $user->can('contract.delete');
    }
}
