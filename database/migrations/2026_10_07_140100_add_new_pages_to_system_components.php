<?php

declare(strict_types=1);

use App\Models\SystemComponent;
use App\Services\MenuService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @return list<array<string, mixed>>
     */
    public static function items(): array
    {
        return [
            ['_parent_key' => 'commercial', 'comp_name' => 'service_contracts', 'comp_ar_label' => 'عقود تقديم الخدمة', 'route_name' => 'dashboard.service-contracts.index', 'prefix' => 'service-contracts', 'icon_name' => 'edit-3', 'icon_class' => 'bi bi-file-earmark-text', 'sort_order' => 6, 'permission_name' => 'company.edit'],
            ['_parent_key' => 'user_management', 'comp_name' => 'support_requests', 'comp_ar_label' => 'طلبات الدعم', 'route_name' => 'dashboard.support-requests.index', 'prefix' => 'support-requests', 'icon_name' => 'life-buoy', 'icon_class' => 'bi bi-life-preserver', 'sort_order' => 3, 'permission_name' => 'user.view'],
            ['_parent_key' => 'user_management', 'comp_name' => 'contact_messages', 'comp_ar_label' => 'رسائل التواصل', 'route_name' => 'dashboard.contact-messages.index', 'prefix' => 'contact-messages', 'icon_name' => 'mail', 'icon_class' => 'bi bi-envelope', 'sort_order' => 4, 'permission_name' => 'user.view'],
            ['_parent_key' => 'settings', 'comp_name' => 'user_manual', 'comp_ar_label' => 'دليل المستخدم', 'route_name' => 'dashboard.manual', 'prefix' => 'manual', 'icon_name' => 'book', 'icon_class' => 'bi bi-book', 'sort_order' => 4, 'permission_name' => 'dashboard.view'],
        ];
    }

    public function up(): void
    {
        if (! Schema::hasTable('system_components')) {
            return;
        }

        $roots = SystemComponent::query()->whereNull('parent_id')->pluck('id', 'comp_name');
        if ($roots->isEmpty()) {
            return;
        }

        foreach (self::items() as $item) {
            $parentId = $roots[$item['_parent_key']] ?? null;
            unset($item['_parent_key']);

            if ($parentId === null || SystemComponent::query()->where('comp_name', $item['comp_name'])->exists()) {
                continue;
            }

            SystemComponent::query()->create($item + ['comp_type' => 3, 'parent_id' => $parentId, 'is_active' => true]);
        }

        SystemComponent::fixTree();
        app(MenuService::class)->clearAllMenuCache();
    }

    public function down(): void
    {
        if (! Schema::hasTable('system_components')) {
            return;
        }

        SystemComponent::query()->whereIn('comp_name', array_column(self::items(), 'comp_name'))->forceDelete();
        SystemComponent::fixTree();
    }
};
