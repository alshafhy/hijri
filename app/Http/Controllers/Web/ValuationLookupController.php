<?php

declare(strict_types=1);

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GeoCity;
use App\Models\Offer;
use App\Models\Partner;
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

    public function partnerOffers(Partner $partner): JsonResponse
    {
        return response()->json(
            Offer::query()->where('partner_id', $partner->id)->orderByDesc('id')->get(['id', 'number', 'offered_at'])
                ->map(fn (Offer $o): array => ['id' => $o->id, 'name' => trim(($o->number ?: '#'.$o->id).' '.($o->offered_at?->format('Y-m-d') ?? ''))])
        );
    }

    public function offerEstates(Offer $offer): JsonResponse
    {
        return response()->json(
            $offer->estates()->orderBy('id')->get()
                ->map(fn ($e): array => [
                    'id' => $e->id,
                    'name' => collect([$e->estate_kind, $e->estate_type, $e->instrument_no, $e->neighborhood])->filter()->implode(' - ') ?: '#'.$e->id,
                ])
        );
    }
}
