<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Actions\Geo\CreateGeoCityAction;
use App\Actions\Geo\DeleteGeoCityAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreGeoCityRequest;
use App\Models\GeoCity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Laracasts\Flash\Flash;

class GeoCityController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', GeoCity::class);

        $cities = GeoCity::query()
            ->withCount('neighborhoods')
            ->when($request->filled('q'), function ($query) use ($request): void {
                $term = '%'.trim((string) $request->string('q')).'%';
                $query->where(function ($q) use ($term): void {
                    $q->where('name_ar', 'like', $term)
                        ->orWhere('name_en', 'like', $term);
                });
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('geo_cities.index', compact('cities'));
    }

    public function create(): View
    {
        $this->authorize('create', GeoCity::class);

        return view('geo_cities.create');
    }

    public function store(StoreGeoCityRequest $request, CreateGeoCityAction $action): RedirectResponse
    {
        $city = $action($request->validated());

        Flash::success(__('messages.saved', ['model' => __('models/geo_cities.singular')]));

        return redirect()->route('dashboard.geo-cities.show', $city);
    }

    public function show(GeoCity $geoCity): View
    {
        $this->authorize('view', $geoCity);

        $neighborhoods = $geoCity->neighborhoods()
            ->orderBy('name_ar')
            ->paginate(50);

        return view('geo_cities.show', [
            'city' => $geoCity,
            'neighborhoods' => $neighborhoods,
        ]);
    }

    public function destroy(GeoCity $geoCity, DeleteGeoCityAction $action): RedirectResponse
    {
        $this->authorize('delete', $geoCity);

        $action($geoCity);

        Flash::success(__('messages.deleted', ['model' => __('models/geo_cities.singular')]));

        return redirect()->route('dashboard.geo-cities.index');
    }
}
