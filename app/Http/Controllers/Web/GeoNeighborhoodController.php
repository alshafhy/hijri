<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Geo\CreateGeoNeighborhoodAction;
use App\Actions\Geo\DeleteGeoNeighborhoodAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreGeoNeighborhoodRequest;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Support\Query\AppliesListSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class GeoNeighborhoodController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GeoNeighborhood::class);

        $query = GeoNeighborhood::query()
            ->with('city:id,name_ar,name_en')
            ->when($request->filled('q'), function ($builder) use ($request): void {
                $term = '%'.trim((string) $request->string('q')).'%';
                $builder->where(function ($q) use ($term): void {
                    $q->where('name_ar', 'like', $term)
                        ->orWhere('name_en', 'like', $term);
                });
            })
            ->when($request->filled('city_id'), fn ($q) => $q->where('city_id', (int) $request->input('city_id')));

        AppliesListSort::apply($query, $request, ['id', 'name_ar', 'name_en', 'city_id', 'created_at']);

        $neighborhoods = $query->paginate(25)->withQueryString();
        $cities = GeoCity::query()->orderBy('name_ar')->pluck('name_ar', 'id');
        $sortMeta = AppliesListSort::current($request);

        return view('geo_neighborhoods.index', compact('neighborhoods', 'cities') + $sortMeta);
    }

    public function store(
        StoreGeoNeighborhoodRequest $request,
        CreateGeoNeighborhoodAction $action
    ): RedirectResponse {
        $neighborhood = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/geo_neighborhoods.singular')]));

        if ($request->boolean('return_to_index')) {
            return redirect()->route('dashboard.geo-neighborhoods.index', ['city_id' => $neighborhood->city_id]);
        }

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

        if (url()->previous() && str_contains((string) url()->previous(), 'geo-neighborhoods')) {
            return redirect()->route('dashboard.geo-neighborhoods.index', ['city_id' => $cityId]);
        }

        return redirect()->route('dashboard.geo-cities.show', $cityId);
    }
}
