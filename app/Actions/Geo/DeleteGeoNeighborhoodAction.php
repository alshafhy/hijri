<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\Models\GeoNeighborhood;

final class DeleteGeoNeighborhoodAction
{
    public function __invoke(GeoNeighborhood $neighborhood): void
    {
        $neighborhood->delete();

        activity('GeoNeighborhood')
            ->performedOn($neighborhood)
            ->withProperties(['action' => 'deleted'])
            ->log('Neighborhood deleted');
    }
}
