<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Enums\Valuation\RequestState;
use App\Models\ValuationRequest;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

final class BuildDashboardChartsAction
{
    /**
     * @param  Builder<ValuationRequest>|null  $query
     * @return array{monthly: array{labels: list<string>, values: list<int>}, by_state: array{labels: list<string>, values: list<int>}}
     */
    public function execute(?Builder $query = null): array
    {
        $query ??= ValuationRequest::query();

        return [
            'monthly' => $this->monthly((clone $query)),
            'by_state' => $this->byState((clone $query)),
        ];
    }

    /**
     * @param  Builder<ValuationRequest>  $query
     * @return array{labels: list<string>, values: list<int>}
     */
    private function monthly(Builder $query): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);
        $expr = $this->monthExpression();

        $counts = $query
            ->where('created_at', '>=', $start)
            ->selectRaw("{$expr} as month_key")
            ->selectRaw('COUNT(*) as aggregate')
            ->groupByRaw($expr)
            ->pluck('aggregate', 'month_key');

        $labels = [];
        $values = [];
        for ($month = $start->copy(); $month->lte(now()); $month->addMonth()) {
            $labels[] = $month->format('Y/m');
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @param  Builder<ValuationRequest>  $query
     * @return array{labels: list<string>, values: list<int>}
     */
    private function byState(Builder $query): array
    {
        $counts = $query
            ->select('state')
            ->selectRaw('COUNT(*) as aggregate')
            ->groupBy('state')
            ->pluck('aggregate', 'state');

        $labels = [];
        $values = [];
        foreach ($counts as $state => $total) {
            $labels[] = RequestState::labelFor(is_string($state) && $state !== '' ? $state : null);
            $values[] = (int) $total;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function monthExpression(): string
    {
        return Schema::getConnection()->getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', created_at)"
            : "DATE_FORMAT(created_at, '%Y-%m')";
    }
}
