<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\Models\GeoNeighborhood;
use Illuminate\Support\Facades\DB;

final class CreateGeoNeighborhoodAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): GeoNeighborhood
    {
        return DB::transaction(function () use ($data): GeoNeighborhood {
            $maxLegacy = (int) GeoNeighborhood::query()->max('legacy_id');

            return GeoNeighborhood::query()->create([
                'legacy_id' => $maxLegacy + 1,
                'city_id' => $data['city_id'],
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
            ]);
        });
    }
}
