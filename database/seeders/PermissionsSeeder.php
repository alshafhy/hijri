<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'user.view',
            'user.create',
            'user.edit',
            'user.delete',
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
            'system_component.view',
            'system_component.edit',
            'branch.view',
            'branch.create',
            'branch.edit',
            'branch.delete',
            'system_release.view',
            'system_release.create',
            'system_release.edit',
            'system_release.delete',
            'valuation_request.view',
            'valuation_request.create',
            'valuation_request.edit',
            'valuation_request.approve_final',
            'valuation_request.unapprove',
            'valuation_request.mark_evaluated',
            'valuation_request.qima_upload',
            'valuation_request.qima_lock',
            'dashboard.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate(
            ['name' => 'super-admin', 'guard_name' => 'web'],
            ['ar_name' => 'مدير النظام']
        );

        $admin = Role::firstOrCreate(
            ['name' => 'admin', 'guard_name' => 'web'],
            ['ar_name' => 'مدير']
        );

        $manager = Role::firstOrCreate(
            ['name' => 'manager', 'guard_name' => 'web'],
            ['ar_name' => 'مدير التقييم']
        );

        $coordinator = Role::firstOrCreate(
            ['name' => 'coordinator', 'guard_name' => 'web'],
            ['ar_name' => 'منسق']
        );

        $evaluator = Role::firstOrCreate(
            ['name' => 'evaluator', 'guard_name' => 'web'],
            ['ar_name' => 'مقيم']
        );

        $superAdmin->syncPermissions($permissions);

        $adminPermissions = array_values(array_filter(
            $permissions,
            fn (string $permission): bool => ! in_array($permission, [
                'system_component.edit',
                'role.delete',
                'user.delete',
            ], true)
        ));
        $admin->syncPermissions($adminPermissions);

        $manager->syncPermissions([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.create',
            'valuation_request.edit',
            'valuation_request.approve_final',
            'valuation_request.unapprove',
            'valuation_request.mark_evaluated',
            'valuation_request.qima_upload',
            'valuation_request.qima_lock',
            'user.view',
            'branch.view',
        ]);

        $coordinator->syncPermissions([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.create',
            'valuation_request.edit',
            'valuation_request.qima_upload',
            'user.view',
        ]);

        $evaluator->syncPermissions([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.edit',
            'valuation_request.mark_evaluated',
        ]);
    }
}
