<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\Enums\Valuation\RequestState;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;

/**
 * @phpstan-type DueRow array{request: ValuationRequest, type: string, client: ?string, fees: float, coordinator: ?string, percent: int, amount: float}
 */
final class BuildStaffProfileAction
{
    private const DUES_PER_PAGE = 25;

    /**
     * @return array<string, mixed> counters, monthly, dues (paginated DueRow) and dues_total
     */
    public function execute(User $member, int $page = 1): array
    {
        $dues = collect($this->dues($member));

        /** @var LengthAwarePaginator<int, DueRow> $paginator */
        $paginator = new LengthAwarePaginator(
            $dues->forPage($page, self::DUES_PER_PAGE)->values(),
            $dues->count(),
            self::DUES_PER_PAGE,
            $page,
            ['path' => request()->url(), 'pageName' => 'dues_page'],
        );

        return [
            'counters' => $this->counters($member),
            'monthly' => $this->monthly($member),
            'dues' => $paginator,
            'dues_total' => round((float) $dues->sum('amount'), 2),
        ];
    }

    /**
     * @return Builder<ValuationRequest>
     */
    private function assigned(User $member): Builder
    {
        return ValuationRequest::query()->where(function (Builder $query) use ($member): void {
            $query->where('coordinator_user_id', $member->id)->orWhere('evaluator_user_id', $member->id);
        });
    }

    /**
     * @return array{total: int, under_evaluation: int, evaluated: int, approved: int, cancelled: int}
     */
    private function counters(User $member): array
    {
        $counters = ['total' => 0, 'under_evaluation' => 0, 'evaluated' => 0, 'approved' => 0, 'cancelled' => 0];

        $rows = $this->assigned($member)
            ->selectRaw('state, approve, COUNT(*) as aggregate')
            ->groupBy('state', 'approve')
            ->get();

        foreach ($rows as $row) {
            $count = (int) $row->getAttribute('aggregate');
            $counters['total'] += $count;

            $bucket = match (true) {
                (int) $row->approve === 1 => 'approved',
                default => match (RequestState::tryFromLegacy($row->state)) {
                    RequestState::UnderEvaluation, RequestState::Waiting2 => 'under_evaluation',
                    RequestState::Evaluated, RequestState::Evaluative => 'evaluated',
                    RequestState::Approve => 'approved',
                    RequestState::Cancelled, RequestState::Delete => 'cancelled',
                    default => null,
                },
            };

            if ($bucket !== null) {
                $counters[$bucket] += $count;
            }
        }

        return $counters;
    }

    /**
     * @return array{labels: list<string>, values: list<int>}
     */
    private function monthly(User $member): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $counts = $this->assigned($member)
            ->where('started_at', '>=', $start)
            ->pluck('started_at')
            ->countBy(fn ($date): string => Carbon::parse($date)->format('Y-m'));

        $labels = [];
        $values = [];
        for ($month = $start->copy(); $month->lte(now()); $month->addMonth()) {
            $labels[] = $month->format('Y/m');
            $values[] = (int) ($counts[$month->format('Y-m')] ?? 0);
        }

        return ['labels' => $labels, 'values' => $values];
    }

    /**
     * @return list<DueRow>
     */
    private function dues(User $member): array
    {
        return $this->assigned($member)
            ->whereHas('feeShares')
            ->with([
                'feeShares',
                'coordinator:id,name',
                'contracts.contractor:id,name,fees',
                'partnerContracts.partner:id,name',
                'partnerContracts.offerEstate:id,fees',
            ])
            ->latest('id')
            ->get()
            ->toBase()
            ->map(function (ValuationRequest $request) use ($member): array {
                $share = $request->feeShares->first();
                $percent = 0;
                if ((int) $request->coordinator_user_id === $member->id) {
                    $percent += (int) $share?->coordinator_share;
                }
                if ((int) $request->evaluator_user_id === $member->id) {
                    $percent += (int) $share?->evaluator_share;
                }

                $contract = $request->contracts->first();
                $partnerContract = $request->partnerContracts->first();
                $fees = (float) ($contract->contractor->fees ?? $partnerContract->offerEstate->fees ?? 0);

                return [
                    'request' => $request,
                    'type' => (string) ($contract !== null ? __('Contractor') : ($partnerContract !== null ? __('Client') : '—')),
                    'client' => $contract->contractor->name ?? $partnerContract->partner->name ?? null,
                    'fees' => $fees,
                    'coordinator' => $request->coordinator?->name,
                    'percent' => $percent,
                    'amount' => round($fees * $percent / 100, 2),
                ];
            })
            ->values()
            ->all();
    }
}
