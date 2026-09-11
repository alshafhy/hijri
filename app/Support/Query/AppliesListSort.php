<?php

declare(strict_types=1);

namespace App\Support\Query;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

final class AppliesListSort
{
    /**
     * @param  list<string>  $allowed
     */
    public static function apply(
        Builder $query,
        Request $request,
        array $allowed,
        string $defaultColumn = 'id',
        string $defaultDirection = 'desc'
    ): Builder {
        $sort = (string) $request->input('sort', $defaultColumn);
        $direction = strtolower((string) $request->input('dir', $defaultDirection)) === 'asc' ? 'asc' : 'desc';

        if (! in_array($sort, $allowed, true)) {
            $sort = $defaultColumn;
            $direction = $defaultDirection === 'asc' ? 'asc' : 'desc';
        }

        return $query->orderBy($sort, $direction);
    }

    /**
     * @return array{sort: string, dir: string}
     */
    public static function current(Request $request, string $defaultColumn = 'id', string $defaultDirection = 'desc'): array
    {
        return [
            'sort' => (string) $request->input('sort', $defaultColumn),
            'dir' => strtolower((string) $request->input('dir', $defaultDirection)) === 'asc' ? 'asc' : 'desc',
        ];
    }
}
