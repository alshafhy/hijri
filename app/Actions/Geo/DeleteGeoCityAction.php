<?php

declare(strict_types=1);

namespace App\Actions\Geo;

use App\Models\GeoCity;
use Illuminate\Support\Facades\DB;

final class DeleteGeoCityAction
{
    public function __invoke(GeoCity $city): void
    {
        DB::transaction(function () use ($city): void {
            $city->neighborhoods()->delete();
            $city->delete();

            activity('GeoCity')
                ->performedOn($city)
                ->withProperties(['action' => 'deleted'])
                ->log('City deleted');
        });
    }
}
