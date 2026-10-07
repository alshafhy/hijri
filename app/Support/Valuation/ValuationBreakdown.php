<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Models\Property;

/**
 * Read model for the detail page valuation table: grid rows with saved totals and land/building share.
 */
final class ValuationBreakdown
{
    /**
     * @return array{
     *     rows: list<array{key: string, title: string, area: float|null, price: float|null, total: float}>,
     *     total: float, land: float, land_share: float|null, building_share: float|null
     * }
     */
    public static function forProperty(Property $property): array
    {
        $components = $property->components->keyBy('component_key');
        $grid = ValuationFieldGrid::rows((string) $property->property_kind, (string) $property->property_type);
        $titles = array_column($grid, 'title', 'key');

        $keys = array_unique(array_merge(array_keys($titles), $components->keys()->map(fn ($k): string => (string) $k)->all()));

        $rows = [];
        $total = 0.0;
        $land = 0.0;
        foreach ($keys as $key) {
            $component = $components->get($key);
            $area = $component?->area_value !== null ? (float) $component->area_value : null;
            $price = $component?->price_value !== null ? (float) $component->price_value : null;
            if (($area ?? 0.0) == 0.0 && ($price ?? 0.0) == 0.0) {
                continue;
            }
            $rowTotal = ($area ?? 0.0) * ($price ?? 0.0);
            $total += $rowTotal;
            if ($land === 0.0 && in_array($key, ValuationFieldGrid::LAND_KEYS, true) && $rowTotal > 0) {
                $land = $rowTotal;
            }
            $rows[] = ['key' => (string) $key, 'title' => $titles[$key] ?? (string) $key, 'area' => $area, 'price' => $price, 'total' => $rowTotal];
        }

        return [
            'rows' => $rows,
            'total' => $total,
            'land' => $land,
            'land_share' => $total > 0 ? round($land / $total * 100, 2) : null,
            'building_share' => $total > 0 ? round(($total - $land) / $total * 100, 2) : null,
        ];
    }
}
