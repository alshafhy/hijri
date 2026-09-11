<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class ListSoftDeletedValuationsAction
{
    public function execute(User $actor, int $perPage = 25): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewDeleted', ValuationRequest::class);

        return ValuationRequest::onlyTrashed()
            ->with(['property', 'coordinator', 'evaluator'])
            ->latest('deleted_at')
            ->paginate($perPage);
    }
}
