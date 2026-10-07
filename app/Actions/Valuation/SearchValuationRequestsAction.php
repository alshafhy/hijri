<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

final class SearchValuationRequestsAction
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function execute(User $actor, array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        Gate::forUser($actor)->authorize('viewAny', ValuationRequest::class);

        $query = ValuationRequest::query()
            ->with(['property.location', 'coordinator', 'evaluator']);

        if (! $actor->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            $query->where(function (Builder $q) use ($actor): void {
                $q->where('coordinator_user_id', $actor->id)
                    ->orWhere('evaluator_user_id', $actor->id)
                    ->orWhere('sub_user_id', $actor->id)
                    ->orWhere('fellow_user_id', $actor->id);
            });
        }

        if (! empty($filters['q'])) {
            $term = '%'.trim((string) $filters['q']).'%';
            $query->where(function (Builder $q) use ($term): void {
                $q->where('number', 'like', $term)
                    ->orWhere('reference', 'like', $term)
                    ->orWhere('deposit_number', 'like', $term)
                    ->orWhereHas('property', function (Builder $pq) use ($term): void {
                        $pq->where('customer_name', 'like', $term)
                            ->orWhere('owner_name', 'like', $term)
                            ->orWhere('instrument_no', 'like', $term);
                    });
            });
        }

        if (! empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (isset($filters['uploaded_on_qima']) && $filters['uploaded_on_qima'] !== '') {
            $query->where('uploaded_on_qima', (bool) $filters['uploaded_on_qima']);
        }

        if (! empty($filters['evaluator_user_id'])) {
            $query->where('evaluator_user_id', (int) $filters['evaluator_user_id']);
        }

        if (! empty($filters['coordinator_user_id'])) {
            $query->where('coordinator_user_id', (int) $filters['coordinator_user_id']);
        }

        if (! empty($filters['property_type'])) {
            $query->whereHas('property', fn (Builder $q) => $q->where('property_type', $filters['property_type']));
        }

        if (! empty($filters['property_kind'])) {
            $query->whereHas('property', fn (Builder $q) => $q->where('property_kind', $filters['property_kind']));
        }

        if (! empty($filters['city_id'])) {
            $query->whereHas('property.location', fn (Builder $q) => $q->where('city_id', (int) $filters['city_id']));
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        if (! empty($filters['approved_only'])) {
            $query->where(function (Builder $q): void {
                $q->where('approve', 1)
                    ->orWhereIn('state', ['approve', 'تم الاعتماد', 'تم الإعتماد']);
            });
        }

        $allowedSorts = ['id', 'reference', 'number', 'state', 'created_at', 'started_at', 'evaluated_at', 'ended_at'];
        $sort = (string) ($filters['sort'] ?? 'id');
        $dir = strtolower((string) ($filters['dir'] ?? 'desc')) === 'asc' ? 'asc' : 'desc';
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
            $dir = 'desc';
        }

        return $query->orderBy($sort, $dir)->paginate($perPage)->withQueryString();
    }
}
