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
            ['_parent_key' => 'valuation', 'comp_name' => 'valuations_map', 'comp_ar_label' => 'خريطة التقييمات', 'route_name' => 'dashboard.valuation-requests.map', 'prefix' => 'valuation-requests/map', 'icon_name' => 'map', 'icon_class' => 'bi bi-geo-alt', 'sort_order' => 8, 'permission_name' => 'valuation_request.view'],
            ['_parent_key' => 'settings', 'comp_name' => 'system_releases', 'comp_ar_label' => 'الإصدارات', 'route_name' => 'dashboard.releases', 'prefix' => 'releases', 'icon_name' => 'git-branch', 'icon_class' => 'bi bi-clock-history', 'sort_order' => 5, 'permission_name' => 'dashboard.view'],
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
