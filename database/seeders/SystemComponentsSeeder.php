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
                'permission_name' => 'dashboard.view',
            ],
            [
                'comp_name' => 'valuation',
                'comp_ar_label' => 'التقييم العقاري',
                'comp_type' => 2,
                'route_name' => null,
                'prefix' => 'valuation',
                'parent_id' => null,
                'icon_name' => 'file-text',
                'icon_class' => 'bi bi-building',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => null,
            ],
            [
                'comp_name' => 'commercial',
                'comp_ar_label' => 'التجاري',
                'comp_type' => 2,
                'route_name' => null,
                'prefix' => 'commercial',
                'parent_id' => null,
                'icon_name' => 'briefcase',
                'icon_class' => 'bi bi-briefcase',
                'sort_order' => 3,
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
                'sort_order' => 4,
                'is_active' => true,
                'permission_name' => null,
            ],
            [
                'comp_name' => 'settings',
                'comp_ar_label' => 'الإعدادات',
                'comp_type' => 2,
                'route_name' => null,
                'prefix' => 'settings',
                'parent_id' => null,
                'icon_name' => 'gear',
                'icon_class' => 'bi bi-gear',
                'sort_order' => 5,
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
                '_parent_key' => 'valuation',
                'comp_name' => 'valuation_requests',
                'comp_ar_label' => 'طلبات التقييم',
                'comp_type' => 3,
                'route_name' => 'dashboard.valuation-requests.index',
                'prefix' => 'valuation-requests',
                'parent_id' => null,
                'icon_name' => 'list',
                'icon_class' => 'bi bi-list-ul',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => 'valuation_request.view',
            ],
            [
                '_parent_key' => 'valuation',
                'comp_name' => 'valuation_advanced_search',
                'comp_ar_label' => 'بحث متقدم',
                'comp_type' => 3,
                'route_name' => 'dashboard.valuation-requests.advanced-search',
                'prefix' => 'valuation-requests',
                'parent_id' => null,
                'icon_name' => 'search',
                'icon_class' => 'bi bi-search',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => 'valuation_request.advanced_search',
            ],
            [
                '_parent_key' => 'valuation',
                'comp_name' => 'valuation_deleted',
                'comp_ar_label' => 'التقييمات المحذوفة',
                'comp_type' => 3,
                'route_name' => 'dashboard.valuation-requests.deleted',
                'prefix' => 'valuation-requests',
                'parent_id' => null,
                'icon_name' => 'trash',
                'icon_class' => 'bi bi-trash',
                'sort_order' => 3,
                'is_active' => true,
                'permission_name' => 'valuation_request.view_deleted',
            ],
            [
                '_parent_key' => 'valuation',
                'comp_name' => 'valuation_qima_pending',
                'comp_ar_label' => 'غير مرفوع على قيمة',
                'comp_type' => 3,
                'route_name' => 'dashboard.valuation-requests.qima-pending',
                'prefix' => 'valuation-requests',
                'parent_id' => null,
                'icon_name' => 'cloud',
                'icon_class' => 'bi bi-cloud-upload',
                'sort_order' => 4,
                'is_active' => true,
                'permission_name' => 'valuation_request.qima_upload',
            ],
            [
                '_parent_key' => 'valuation',
                'comp_name' => 'valuation_activity_logs',
                'comp_ar_label' => 'سجل نشاط التقييم',
                'comp_type' => 3,
                'route_name' => 'dashboard.valuation-requests.activity-logs',
                'prefix' => 'valuation-requests',
                'parent_id' => null,
                'icon_name' => 'activity',
                'icon_class' => 'bi bi-journal-text',
                'sort_order' => 5,
                'is_active' => true,
                'permission_name' => 'valuation_request.view_logs',
            ],
            // contracts menu → contractors index (legacy naming)
            [
                '_parent_key' => 'commercial',
                'comp_name' => 'contractors',
                'comp_ar_label' => 'العقود',
                'comp_type' => 3,
                'route_name' => 'dashboard.contractors.index',
                'prefix' => 'contractors',
                'parent_id' => null,
                'icon_name' => 'file-earmark-text',
                'icon_class' => 'bi bi-file-earmark-text',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => 'contractor.view',
            ],
            [
                '_parent_key' => 'commercial',
                'comp_name' => 'partners',
                'comp_ar_label' => 'العملاء',
                'comp_type' => 3,
                'route_name' => 'dashboard.partners.index',
                'prefix' => 'partners',
                'parent_id' => null,
                'icon_name' => 'people',
                'icon_class' => 'bi bi-people',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => 'partner.view',
            ],
            [
                '_parent_key' => 'commercial',
                'comp_name' => 'offers',
                'comp_ar_label' => 'العروض',
                'comp_type' => 3,
                'route_name' => 'dashboard.offers.index',
                'prefix' => 'offers',
                'parent_id' => null,
                'icon_name' => 'tag',
                'icon_class' => 'bi bi-tag',
                'sort_order' => 3,
                'is_active' => true,
                'permission_name' => 'offer.view',
            ],
            [
                '_parent_key' => 'commercial',
                'comp_name' => 'valuation_contracts',
                'comp_ar_label' => 'عقود التقييم',
                'comp_type' => 3,
                'route_name' => 'dashboard.contracts.index',
                'prefix' => 'contracts',
                'parent_id' => null,
                'icon_name' => 'journal-check',
                'icon_class' => 'bi bi-journal-check',
                'sort_order' => 4,
                'is_active' => true,
                'permission_name' => 'contract.view',
            ],
            [
                '_parent_key' => 'commercial',
                'comp_name' => 'financial',
                'comp_ar_label' => 'المالية',
                'comp_type' => 3,
                'route_name' => 'dashboard.financial.index',
                'prefix' => 'financial',
                'parent_id' => null,
                'icon_name' => 'cash-stack',
                'icon_class' => 'bi bi-cash-stack',
                'sort_order' => 5,
                'is_active' => true,
                'permission_name' => 'financial.view',
            ],
            [
                '_parent_key' => 'settings',
                'comp_name' => 'geo_cities',
                'comp_ar_label' => 'المدن',
                'comp_type' => 3,
                'route_name' => 'dashboard.geo-cities.index',
                'prefix' => 'geo-cities',
                'parent_id' => null,
                'icon_name' => 'geo-alt',
                'icon_class' => 'bi bi-geo-alt',
                'sort_order' => 1,
                'is_active' => true,
                'permission_name' => 'geo_city.view',
            ],
            [
                '_parent_key' => 'settings',
                'comp_name' => 'geo_neighborhoods',
                'comp_ar_label' => 'الأحياء',
                'comp_type' => 3,
                'route_name' => 'dashboard.geo-neighborhoods.index',
                'prefix' => 'geo-neighborhoods',
                'parent_id' => null,
                'icon_name' => 'pin-map',
                'icon_class' => 'bi bi-pin-map',
                'sort_order' => 2,
                'is_active' => true,
                'permission_name' => 'geo_neighborhood.view',
            ],
            [
                '_parent_key' => 'settings',
                'comp_name' => 'company_profile',
                'comp_ar_label' => 'ملف الشركة',
                'comp_type' => 3,
                'route_name' => 'dashboard.companies.index',
                'prefix' => 'companies',
                'parent_id' => null,
                'icon_name' => 'building',
                'icon_class' => 'bi bi-building',
                'sort_order' => 3,
                'is_active' => true,
                'permission_name' => 'company.view',
            ],
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
