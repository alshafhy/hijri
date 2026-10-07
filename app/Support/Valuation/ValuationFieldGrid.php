<?php

declare(strict_types=1);

namespace App\Support\Valuation;

/**
 * Area × price rows per property kind/subtype, driven by config/constants-fields.php.
 */
final class ValuationFieldGrid
{
    /** Component keys whose row total is the land value (first match wins). */
    public const LAND_KEYS = ['land_area', 'share_land'];

    /**
     * @return list<array{key: string, title: string}>
     */
    public static function rows(?string $kind, ?string $type): array
    {
        $kind = trim((string) $kind);
        $type = trim((string) $type);

        $fields = config("constants-fields.fields.{$kind}.{$type}.area")
            ?: config("constants-fields.fields.{$kind}.default.area", []);

        $rows = [];
        $seen = [];
        foreach ((array) $fields as $field) {
            $areaName = (string) ($field['field_area_name'] ?? '');
            if ($areaName === '') {
                continue;
            }
            $key = ComponentKeyMap::fromAreaColumn($areaName);
            if (isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $rows[] = [
                'key' => $key,
                'title' => trim((string) ($field['field_title'] ?? $key)),
            ];
        }

        return $rows;
    }

    /**
     * @return array<string, list<string>>
     */
    public static function kinds(): array
    {
        $types = config('constants.select.real_estate_types', []);
        $out = [];
        foreach ($types as $kind => $def) {
            $out[(string) $kind] = array_values(array_map(
                static fn ($v): string => trim((string) $v),
                (array) ($def['options'] ?? [])
            ));
        }

        return $out;
    }

    public static function isBuilding(?string $kind): bool
    {
        return ! in_array(trim((string) $kind), ['أرض', 'صناعي', 'زراعي', 'مرفق', ''], true);
    }
}
