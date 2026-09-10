<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SystemComponent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemComponentsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('system_components')->delete();

        $roots = [];

        foreach ($this->groups() as $data) {
            $roots[$data['comp_name']] = SystemComponent::create($data);
        }

        foreach ($this->leaves() as $data) {
            $parentKey = $data['_parent_key'];
            unset($data['_parent_key']);

            if (isset($roots[$parentKey])) {
                $data['parent_id'] = $roots[$parentKey]->id;
            }

            SystemComponent::create($data);
        }

        SystemComponent::fixTree();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function groups(): array
    {
        return [
            [
                'comp_name' => 'dashboard',
                'comp_ar_label' => 'لوحة التحكم',
                'comp_type' => 3,
                'route_name' => 'home',
                'prefix' => 'home',
                'parent_id' => null,
                'icon_name' => 'home',
                'icon_class' => 'bi bi-house-door',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => null,
            ],
            [
                'comp_name' => 'user_management',
                'comp_ar_label' => 'إدارة المستخدمين',
                'comp_type' => 2,
                'route_name' => null,
                'prefix' => 'users',
                'parent_id' => null,
                'icon_name' => 'users',
                'icon_class' => 'bi bi-people',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => null,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function leaves(): array
    {
        return [
            [
                '_parent_key' => 'user_management',
                'comp_name' => 'users',
                'comp_ar_label' => 'المستخدمون',
                'comp_type' => 3,
                'route_name' => 'dashboard.users.index',
                'prefix' => 'users',
                'parent_id' => null,
                'icon_name' => 'user',
                'icon_class' => 'bi bi-person',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => 'user.view',
            ],
            [
                '_parent_key' => 'user_management',
                'comp_name' => 'roles',
                'comp_ar_label' => 'الأدوار',
                'comp_type' => 3,
                'route_name' => 'dashboard.roles.index',
                'prefix' => 'roles',
                'parent_id' => null,
                'icon_name' => 'shield',
                'icon_class' => 'bi bi-shield-check',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => 'role.view',
            ],
        ];
    }
}
