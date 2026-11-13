<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use Illuminate\Support\Facades\Cache;

final class InvalidateValuationDashboardStatsCacheAction
{
    public const CACHE_KEYS = [
        'valuation.dashboard.approved_count',
        'valuation.dashboard.total_valued_area',
        'valuation.dashboard.avg_turnaround_hours',
    ];

    public function execute(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }
}
