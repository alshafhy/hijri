<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\PropertyComponent;
use App\Models\ValuationRequest;
use Illuminate\Support\Facades\Cache;

final class GetTotalValuedAreaAction
{
    public function execute(): array
    {
        $total = Cache::remember('valuation.dashboard.total_valued_area', 300, function (): float {
            $approvedIds = ValuationRequest::query()
                ->where(function ($q): void {
                    $q->where('approve', 1)
                        ->orWhereIn('state', ['approve', 'تم الاعتماد', 'تم الإعتماد']);
                })
                ->pluck('id');

            if ($approvedIds->isEmpty()) {
                return 0.0;
            }

            return (float) PropertyComponent::query()
                ->whereIn('component_key', ['land_area', 'share_land'])
                ->whereHas('property', function ($q) use ($approvedIds): void {
                    $q->whereIn('valuation_request_id', $approvedIds);
                })
                ->sum('area_value');
        });

        return ['total_valued_area' => $total];
    }
}
