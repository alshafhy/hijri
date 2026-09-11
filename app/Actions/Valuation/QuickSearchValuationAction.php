<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

final class QuickSearchValuationAction
{
    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function execute(User $actor, string $term, int $limit = 15): array
    {
        Gate::forUser($actor)->authorize('viewAny', ValuationRequest::class);

        $term = trim($term);
        if ($term === '') {
            return ['items' => []];
        }

        $like = '%'.$term.'%';

        $query = ValuationRequest::query()
            ->with('property')
            ->where(function (Builder $q) use ($like, $term): void {
                $q->where('number', 'like', $like)
                    ->orWhere('reference', 'like', $like)
                    ->orWhere('id', is_numeric($term) ? (int) $term : 0);
            });

        if (! $actor->hasAnyRole(['super-admin', 'admin', 'manager'])) {
            $query->where(function (Builder $q) use ($actor): void {
                $q->where('coordinator_user_id', $actor->id)
                    ->orWhere('evaluator_user_id', $actor->id);
            });
        }

        /** @var Collection<int, ValuationRequest> $rows */
        $rows = $query->latest('id')->limit($limit)->get();

        return [
            'items' => $rows->map(static fn (ValuationRequest $r): array => [
                'id' => $r->id,
                'number' => $r->number,
                'reference' => $r->reference,
                'state' => $r->state,
                'customer_name' => $r->property?->customer_name,
            ])->all(),
        ];
    }
}
