<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GeoNeighborhood;
use App\Models\User;

class GeoNeighborhoodPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('geo_neighborhood.view');
    }

    public function view(User $user, GeoNeighborhood $geoNeighborhood): bool
    {
        return $user->can('geo_neighborhood.view');
    }

    public function create(User $user): bool
    {
        return $user->can('geo_neighborhood.create');
    }

    public function delete(User $user, GeoNeighborhood $geoNeighborhood): bool
    {
        return $user->can('geo_neighborhood.delete');
    }
}
