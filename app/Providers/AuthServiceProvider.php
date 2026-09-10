<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\User;
use App\Overrides\Spatie\Role;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
