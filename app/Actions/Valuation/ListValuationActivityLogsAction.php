<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

final class ListValuationActivityLogsAction
{
    public function execute(User $actor, ?int $valuationRequestId = null, int $perPage = 40): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewLogs', ValuationRequest::class);

        $query = Activity::query()
            ->where('log_name', 'valuation')
            ->with(['causer', 'subject'])
            ->latest('id');

        if ($valuationRequestId !== null) {
            $query->where('subject_type', ValuationRequest::class)
                ->where('subject_id', $valuationRequestId);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
