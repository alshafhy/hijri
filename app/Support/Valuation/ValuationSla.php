<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Enums\Valuation\RequestState;
use App\Models\Company;
use App\Models\ValuationRequest;
use Illuminate\Support\Carbon;

final class ValuationSla
{
    public const ON_TIME = 'on_time';

    public const OVERDUE = 'overdue';

    private const DEFAULT_HOURS = 5;

    /** @var array<int, Company|null> */
    private array $companies = [];

    /**
     * Returns null when the request is outside the SLA-tracked stages.
     */
    public function status(ValuationRequest $request, ?Carbon $now = null): ?string
    {
        $state = RequestState::tryFromLegacy($request->state);

        [$since, $limit] = match ($state) {
            RequestState::Waiting, RequestState::Request => [
                $request->started_at ?? $request->created_at,
                $this->company($request)?->request_sla_hours,
            ],
            RequestState::UnderEvaluation, RequestState::Waiting2 => [
                $request->under_evaluation_at ?? $request->started_at ?? $request->created_at,
                $this->company($request)?->evaluation_sla_hours,
            ],
            default => [null, null],
        };

        if ($since === null) {
            return null;
        }

        $elapsed = $since->diffInHours($now ?? now(), true);

        return $elapsed < ($limit ?? self::DEFAULT_HOURS) ? self::ON_TIME : self::OVERDUE;
    }

    public function buttonClass(ValuationRequest $request): string
    {
        return match ($this->status($request)) {
            self::ON_TIME => 'btn-outline-success',
            self::OVERDUE => 'btn-danger',
            default => 'btn-primary',
        };
    }

    private function company(ValuationRequest $request): ?Company
    {
        $key = (int) $request->company_id;

        if (! array_key_exists($key, $this->companies)) {
            $this->companies[$key] = $key > 0
                ? Company::query()->find($key)
                : Company::query()->orderBy('id')->first();
        }

        return $this->companies[$key];
    }
}
