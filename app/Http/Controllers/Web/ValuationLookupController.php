<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GeoCity;
use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Http\JsonResponse;

class ValuationLookupController extends Controller
{
    public function neighborhoods(GeoCity $geoCity): JsonResponse
    {
        return response()->json(
            $geoCity->neighborhoods()->orderBy('name_ar')->get(['id', 'name_ar'])
                ->map(fn ($n): array => ['id' => $n->id, 'name' => $n->name_ar])
        );
    }

    public function propertyTypes(string $kind): JsonResponse
    {
        return response()->json(ValuationFieldGrid::kinds()[$kind] ?? []);
    }

    public function gridRows(string $kind, string $type): JsonResponse
    {
        return response()->json(ValuationFieldGrid::rows($kind, $type));
    }
}
