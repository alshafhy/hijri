<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Geo\CreateGeoNeighborhoodAction;
use App\Actions\Geo\DeleteGeoNeighborhoodAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreGeoNeighborhoodRequest;
use App\Models\GeoNeighborhood;
use Illuminate\Http\RedirectResponse;
use Laracasts\Flash\Flash;

class GeoNeighborhoodController extends Controller
{
    public function store(
        StoreGeoNeighborhoodRequest $request,
        CreateGeoNeighborhoodAction $action
    ): RedirectResponse {
        $neighborhood = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/geo_neighborhoods.singular')]));

        return redirect()->route('dashboard.geo-cities.show', $neighborhood->city_id);
    }

    public function destroy(
        GeoNeighborhood $geoNeighborhood,
        DeleteGeoNeighborhoodAction $action
    ): RedirectResponse {
        $this->authorize('delete', $geoNeighborhood);

        $cityId = $geoNeighborhood->city_id;
        $action($geoNeighborhood);

        Flash::success(__('messages.deleted', ['model' => __('models/geo_neighborhoods.singular')]));

        return redirect()->route('dashboard.geo-cities.show', $cityId);
    }
}
