<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\GeoCity;
use App\Models\User;

class GeoCityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('geo_city.view');
    }

    public function view(User $user, GeoCity $geoCity): bool
    {
        return $user->can('geo_city.view');
    }

    public function create(User $user): bool
    {
        return $user->can('geo_city.create');
    }

    public function delete(User $user, GeoCity $geoCity): bool
    {
        return $user->can('geo_city.delete');
    }
}
