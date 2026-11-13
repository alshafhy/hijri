<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Data for the "all valuations on the map" page: markers plus a per-city summary.
 *
 * @phpstan-type MapPoint array{id: int, reference: string, lat: float, lng: float, city: string|null, kind: string|null, type: string|null, customer: string|null, owner: string|null, value: float|null}
 * @phpstan-type MapCity array{id: int, name: string, count: int, total: float}
 */
final class BuildValuationMapAction
{
    public const MAX_POINTS = 5000;

    /**
     * @return array{points: list<MapPoint>, cities: list<MapCity>, total: int, truncated: bool}
     */
    public function execute(User $actor, ?int $cityId = null): array
    {
        Gate::forUser($actor)->authorize('viewAny', ValuationRequest::class);

        $value = 'COALESCE(NULLIF(property_totals.total_amount_manual, 0), property_totals.total_amount)';

        $cities = $this->baseQuery()
            ->join('geo_cities', 'geo_cities.id', '=', 'property_locations.city_id')
            ->groupBy('geo_cities.id', 'geo_cities.name_ar')
            ->orderByDesc('count')
            ->orderBy('geo_cities.name_ar')
            ->get([
                'geo_cities.id',
                'geo_cities.name_ar as name',
                DB::raw('COUNT(*) as count'),
                DB::raw("COALESCE(SUM({$value}), 0) as total"),
            ])
            ->map(fn ($row): array => [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'count' => (int) $row->count,
                'total' => (float) $row->total,
            ])
            ->values()
            ->all();

        $points = $this->baseQuery()
            ->leftJoin('geo_cities', 'geo_cities.id', '=', 'property_locations.city_id')
            ->when($cityId, fn ($q) => $q->where('property_locations.city_id', $cityId))
            ->orderByDesc('valuation_requests.id')
            ->limit(self::MAX_POINTS + 1)
            ->get([
                'valuation_requests.id',
                'valuation_requests.reference',
                'property_locations.x_axis',
                'property_locations.y_axis',
                'geo_cities.name_ar as city',
                'properties.property_kind',
                'properties.property_type',
                'properties.customer_name',
                'properties.owner_name',
                DB::raw("{$value} as value"),
            ]);

        $truncated = $points->count() > self::MAX_POINTS;

        return [
            'points' => $points->take(self::MAX_POINTS)->map(fn ($row): array => [
                'id' => (int) $row->id,
                'reference' => (string) ($row->reference ?? $row->id),
                'lat' => round((float) $row->x_axis, 6),
                'lng' => round((float) $row->y_axis, 6),
                'city' => $row->city,
                'kind' => $row->property_kind,
                'type' => $row->property_type,
                'customer' => $row->customer_name,
                'owner' => $row->owner_name,
                'value' => $row->value !== null ? (float) $row->value : null,
            ])->values()->all(),
            'cities' => $cities,
            'total' => array_sum(array_column($cities, 'count')),
            'truncated' => $truncated,
        ];
    }

    /**
     * Valid coordinates are inside Saudi Arabia's bounding box (lat 16..33, lng 34..56).
     */
    private function baseQuery(): Builder
    {
        return DB::table('valuation_requests')
            ->join('properties', 'properties.valuation_request_id', '=', 'valuation_requests.id')
            ->join('property_locations', 'property_locations.property_id', '=', 'properties.id')
            ->leftJoin('property_totals', 'property_totals.property_id', '=', 'properties.id')
            ->whereNull('valuation_requests.deleted_at')
            ->whereBetween('property_locations.x_axis', [16, 33])
            ->whereBetween('property_locations.y_axis', [34, 56]);
    }
}
