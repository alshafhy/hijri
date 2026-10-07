<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\Models\GeoCity;
use Illuminate\Support\Facades\DB;

final class CreateGeoCityAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): GeoCity
    {
        return DB::transaction(function () use ($data): GeoCity {
            $maxLegacy = (int) GeoCity::query()->max('legacy_id');

            return GeoCity::query()->create([
                'legacy_id' => $maxLegacy + 1,
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'] ?? null,
            ]);
        });
    }
}
