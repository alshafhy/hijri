<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SystemComponent;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class MenuService
{
    /**
     * @return Collection<int, SystemComponent>
     */
    public function getMenuForUser(?User $user): Collection
    {
        if (! $user) {
            return collect();
        }

        return Cache::remember("menu_user_{$user->id}", 3600, function () use ($user) {
            $roots = SystemComponent::with('children')
                ->whereIsRoot()
                ->active()
                ->orderBy('sort_order')
                ->get();

            return $this->filterTree(collect($roots->all()), $user);
        });
    }

    public function clearMenuCache(int $userId): void
    {
        Cache::forget("menu_user_{$userId}");
    }

    public function clearAllMenuCache(): void
    {
        try {
            Cache::tags(['menu'])->flush();
        } catch (\BadMethodCallException) {
            // Driver does not support tags — iterate known users
            User::query()->select('id')->each(
                fn (User $u) => $this->clearMenuCache($u->id)
            );
        }
    }

    /**
     * @param  Collection<int, SystemComponent>  $nodes
     * @return Collection<int, SystemComponent>
     */
    private function filterTree(Collection $nodes, User $user): Collection
    {
        return $nodes
            ->filter(fn (SystemComponent $node) => $node->hasAccess($user))
            ->map(function (SystemComponent $node) use ($user) {
                if ($node->children->isNotEmpty()) {
                    /** @var Collection<int, SystemComponent> $childNodes */
                    $childNodes = collect($node->children->sortBy('sort_order')->values()->all());
                    $visibleChildren = $this->filterTree($childNodes, $user);

                    // Drop groups with no visible children
                    if ($visibleChildren->isEmpty() && empty($node->route_name)) {
                        return null;
                    }

                    $node->setRelation('children', $visibleChildren);
                }

                return $node;
            })
            ->filter()
            ->values();
    }
}
