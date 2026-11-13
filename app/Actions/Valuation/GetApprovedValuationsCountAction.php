<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\ValuationRequest;
use Illuminate\Support\Facades\Cache;

final class GetApprovedValuationsCountAction
{
    public function execute(): array
    {
        $count = Cache::remember('valuation.dashboard.approved_count', 300, function (): int {
            return ValuationRequest::query()
                ->where(function ($q): void {
                    $q->where('approve', 1)
                        ->orWhereIn('state', ['approve', 'تم الاعتماد', 'تم الإعتماد']);
                })
                ->count();
        });

        return ['approved_count' => $count];
    }
}
