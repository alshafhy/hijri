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
            'valuation_request.send',
            'valuation_request.reject',
            'valuation_request.cancel',
            'valuation_request.duplicate',
            'valuation_request.change_evaluator',
            'valuation_request.change_coordinator',
            'valuation_request.change_property_type',
            'valuation_request.override_amount',
            'valuation_request.manage_fee_shares',
            'valuation_request.view_deleted',
            'valuation_request.view_logs',
            'valuation_request.advanced_search',
            'valuation_request.export_pdf',
            'dashboard.view',
            'partner.view',
            'partner.create',
            'partner.edit',
            'partner.delete',
            'partner.activate',
            'contractor.view',
            'contractor.create',
            'contractor.edit',
            'contractor.delete',
            'contractor.activate',
            'contract.view',
            'contract.create',
            'contract.edit',
            'contract.mark_paid',
            'offer.view',
            'offer.create',
            'offer.edit',
            'offer.activate',
            'geo_city.view',
            'geo_city.create',
            'geo_city.delete',
            'geo_neighborhood.view',
            'geo_neighborhood.create',
            'geo_neighborhood.delete',
            'financial.view',
            'company.view',
            'company.edit',
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

        $commercialManager = [
            'partner.view',
            'partner.create',
            'partner.edit',
            'partner.delete',
            'partner.activate',
            'contractor.view',
            'contractor.create',
            'contractor.edit',
            'contractor.delete',
            'contractor.activate',
            'contract.view',
            'contract.create',
            'contract.edit',
            'contract.mark_paid',
            'offer.view',
            'offer.create',
            'offer.edit',
            'offer.activate',
            'geo_city.view',
            'geo_city.create',
            'geo_city.delete',
            'geo_neighborhood.view',
            'geo_neighborhood.create',
            'geo_neighborhood.delete',
            'financial.view',
            'company.view',
            'company.edit',
        ];

        $manager->syncPermissions(array_values(array_unique(array_merge([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.create',
            'valuation_request.edit',
            'valuation_request.approve_final',
            'valuation_request.unapprove',
            'valuation_request.mark_evaluated',
            'valuation_request.qima_upload',
            'valuation_request.qima_lock',
            'valuation_request.send',
            'valuation_request.reject',
            'valuation_request.cancel',
            'valuation_request.duplicate',
            'valuation_request.change_evaluator',
            'valuation_request.change_coordinator',
            'valuation_request.change_property_type',
            'valuation_request.override_amount',
            'valuation_request.manage_fee_shares',
            'valuation_request.view_deleted',
            'valuation_request.view_logs',
            'valuation_request.advanced_search',
            'valuation_request.export_pdf',
            'user.view',
            'branch.view',
        ], $commercialManager))));

        $coordinator->syncPermissions([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.create',
            'valuation_request.edit',
            'valuation_request.qima_upload',
            'valuation_request.send',
            'valuation_request.reject',
            'valuation_request.cancel',
            'valuation_request.duplicate',
            'valuation_request.change_evaluator',
            'valuation_request.change_property_type',
            'valuation_request.manage_fee_shares',
            'valuation_request.advanced_search',
            'valuation_request.export_pdf',
            'user.view',
            'partner.view',
            'partner.create',
            'partner.edit',
            'contractor.view',
            'contractor.create',
            'contractor.edit',
            'contract.view',
            'contract.create',
            'contract.edit',
            'offer.view',
            'offer.create',
            'offer.edit',
            'geo_city.view',
            'geo_city.create',
            'geo_neighborhood.view',
            'geo_neighborhood.create',
        ]);

        $evaluator->syncPermissions([
            'dashboard.view',
            'valuation_request.view',
            'valuation_request.edit',
            'valuation_request.mark_evaluated',
            'valuation_request.advanced_search',
            'valuation_request.export_pdf',
        ]);
    }
}
