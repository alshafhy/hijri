<?php

declare(strict_types=1);

namespace App\Actions\Financial;

use App\Models\Contract;
use App\Models\RequestFeeShare;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

final class BuildFinancialBoardAction
{
    private const CACHE_TTL_SECONDS = 300;

    /**
     * @return array{
     *     month: ?string,
     *     rows: LengthAwarePaginator,
     *     summary: array{
     *         coordinator_share_total: int,
     *         evaluator_share_total: int,
     *         manager_share_total: int,
     *         contractor_fees_paid: int,
     *         contractor_fees_unpaid: int,
     *         requests_count: int
     *     }
     * }
     */
    public function __invoke(?string $month = null, int $perPage = 25): array
    {
        [$year, $monthNumber] = $this->parseMonth($month);

        $rows = ValuationRequest::query()
            ->with([
                'feeShares',
                'contracts.contractor:id,name,fees',
                'coordinator:id,name',
                'evaluator:id,name',
            ])
            ->whereHas('feeShares')
            ->when($year !== null && $monthNumber !== null, function ($query) use ($year, $monthNumber): void {
                $query->whereYear('started_at', $year)->whereMonth('started_at', $monthNumber);
            })
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (ValuationRequest $request): ValuationRequest {
                $share = $request->feeShares->first();
                $contract = $request->contracts->first();

                $request->setAttribute('display_coordinator_name', $request->coordinator?->name);
                $request->setAttribute('display_evaluator_name', $request->evaluator?->name);
                $request->setAttribute('display_coordinator_share', $share?->coordinator_share);
                $request->setAttribute('display_evaluator_share', $share?->evaluator_share);
                $request->setAttribute('display_manager_share', $share?->manager_share);
                $request->setAttribute('display_contractor_name', $contract?->contractor?->name);
                $request->setAttribute('display_contract_state', $contract?->state);

                return $request;
            });

        $summary = Cache::remember(
            $this->summaryCacheKey($month),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->buildSummary($year, $monthNumber)
        );

        return [
            'month' => $month,
            'rows' => $rows,
            'summary' => $summary,
        ];
    }

    /**
     * @return array{0: ?int, 1: ?int}
     */
    private function parseMonth(?string $month): array
    {
        if ($month === null || $month === '') {
            return [null, null];
        }

        $parsed = Carbon::createFromFormat('Y-m', $month);

        return [(int) $parsed->format('Y'), (int) $parsed->format('m')];
    }

    private function summaryCacheKey(?string $month): string
    {
        return 'financial_board_summary:'.($month ?: 'all');
    }

    /**
     * @return array{
     *     coordinator_share_total: int,
     *     evaluator_share_total: int,
     *     manager_share_total: int,
     *     contractor_fees_paid: int,
     *     contractor_fees_unpaid: int,
     *     requests_count: int
     * }
     */
    private function buildSummary(?int $year, ?int $monthNumber): array
    {
        $shares = RequestFeeShare::query()
            ->when($year !== null && $monthNumber !== null, function ($query) use ($year, $monthNumber): void {
                $query->whereHas('valuationRequest', function ($q) use ($year, $monthNumber): void {
                    $q->whereYear('started_at', $year)->whereMonth('started_at', $monthNumber);
                });
            })
            ->selectRaw('COALESCE(SUM(coordinator_share),0) as coordinator_share_total')
            ->selectRaw('COALESCE(SUM(evaluator_share),0) as evaluator_share_total')
            ->selectRaw('COALESCE(SUM(manager_share),0) as manager_share_total')
            ->selectRaw('COUNT(DISTINCT valuation_request_id) as requests_count')
            ->first();

        $paidFees = (int) Contract::query()
            ->join('contractors', 'contractors.id', '=', 'contracts.contractor_id')
            ->where('contracts.state', Contract::STATE_PAID)
            ->when($year !== null && $monthNumber !== null, function ($query) use ($year, $monthNumber): void {
                $query->whereHas('valuationRequest', function ($q) use ($year, $monthNumber): void {
                    $q->whereYear('started_at', $year)->whereMonth('started_at', $monthNumber);
                });
            })
            ->sum('contractors.fees');

        $unpaidFees = (int) Contract::query()
            ->join('contractors', 'contractors.id', '=', 'contracts.contractor_id')
            ->where('contracts.state', Contract::STATE_UNPAID)
            ->when($year !== null && $monthNumber !== null, function ($query) use ($year, $monthNumber): void {
                $query->whereHas('valuationRequest', function ($q) use ($year, $monthNumber): void {
                    $q->whereYear('started_at', $year)->whereMonth('started_at', $monthNumber);
                });
            })
            ->sum('contractors.fees');

        return [
            'coordinator_share_total' => (int) ($shares->coordinator_share_total ?? 0),
            'evaluator_share_total' => (int) ($shares->evaluator_share_total ?? 0),
            'manager_share_total' => (int) ($shares->manager_share_total ?? 0),
            'contractor_fees_paid' => $paidFees,
            'contractor_fees_unpaid' => $unpaidFees,
            'requests_count' => (int) ($shares->requests_count ?? 0),
        ];
    }
}
