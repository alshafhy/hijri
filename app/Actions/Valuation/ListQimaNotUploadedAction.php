<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Gate;

final class ListQimaNotUploadedAction
{
    public function execute(User $actor, int $perPage = 25): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewAny', ValuationRequest::class);

        return ValuationRequest::query()
            ->with(['property', 'evaluator', 'coordinator'])
            ->where('uploaded_on_qima', false)
            ->where(function ($q): void {
                $q->where('approve', 1)
                    ->orWhereIn('state', ['approve', 'تم الاعتماد', 'تم الإعتماد']);
            })
            ->latest('id')
            ->paginate($perPage);
    }
}
