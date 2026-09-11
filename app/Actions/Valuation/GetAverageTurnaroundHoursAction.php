<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\ValuationRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class GetAverageTurnaroundHoursAction
{
    public function execute(): array
    {
        $hours = Cache::remember('valuation.dashboard.avg_turnaround_hours', 300, function (): ?float {
            $driver = DB::connection()->getDriverName();

            $avgExpression = match ($driver) {
                'sqlite' => 'AVG((julianday(ended_at) - julianday(started_at)) * 86400)',
                default => 'AVG(TIMESTAMPDIFF(SECOND, started_at, ended_at))',
            };

            $avgSeconds = ValuationRequest::query()
                ->whereNotNull('started_at')
                ->whereNotNull('ended_at')
                ->where(function ($q): void {
                    $q->where('approve', 1)
                        ->orWhereIn('state', ['approve', 'تم الاعتماد', 'تم الإعتماد']);
                })
                ->select(DB::raw($avgExpression.' as avg_seconds'))
                ->value('avg_seconds');

            if ($avgSeconds === null) {
                return null;
            }

            return round(((float) $avgSeconds) / 3600, 2);
        });

        return ['average_turnaround_hours' => $hours];
    }
}
