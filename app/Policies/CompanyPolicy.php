<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Company;
use App\Models\User;

class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('company.edit') || $user->can('company.view');
    }

    public function view(User $user, Company $company): bool
    {
        return $user->can('company.edit') || $user->can('company.view');
    }

    public function update(User $user, Company $company): bool
    {
        return $user->can('company.edit');
    }
}
