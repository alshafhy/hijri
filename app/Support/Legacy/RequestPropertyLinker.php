<?php

declare(strict_types=1);

namespace App\Support\Legacy;

use Illuminate\Support\Facades\DB;
use stdClass;

/**
 * Resolves request ↔ property (REI) links without guessing.
 * Old schema joins only via shared Location id — never carry that forward.
 */
final class RequestPropertyLinker
{
    public const RESOLUTION_CERTAIN = 'certain';

    public const RESOLUTION_AMBIGUOUS = 'ambiguous';

    public const RESOLUTION_ORPHAN = 'orphan';

    /**
     * @return array{
     *   resolution: string,
     *   reason: string,
     *   request_id: int,
     *   location_id: int|null,
     *   rei_id: int|null,
     *   rei_ids: list<int>,
     *   sibling_request_ids: list<int>
     * }
     */
    public function resolve(stdClass $request, string $connection = 'legacy'): array
    {
        $requestId = (int) $request->idRequest;
        $locationId = isset($request->Location) ? (int) $request->Location : 0;

        if ($locationId <= 0) {
            return $this->result(self::RESOLUTION_ORPHAN, 'request_missing_location', $requestId, null, null, [], []);
        }

        $locationExists = DB::connection($connection)
            ->table('location')
            ->where('id', $locationId)
            ->exists();

        if (! $locationExists) {
            return $this->result(self::RESOLUTION_ORPHAN, 'location_row_missing', $requestId, $locationId, null, [], []);
        }

        $siblingRequestIds = DB::connection($connection)
            ->table('request')
            ->where('Location', $locationId)
            ->where('idRequest', '!=', $requestId)
            ->pluck('idRequest')
            ->map(fn ($id) => (int) $id)
            ->all();

        $reiIds = DB::connection($connection)
            ->table('real_estate_information')
            ->where('Location', $locationId)
            ->pluck('idReal_Estate_Information')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($siblingRequestIds !== []) {
            return $this->result(
                self::RESOLUTION_AMBIGUOUS,
                'location_shared_by_multiple_requests',
                $requestId,
                $locationId,
                null,
                $reiIds,
                $siblingRequestIds
            );
        }

        if (count($reiIds) > 1) {
            return $this->result(
                self::RESOLUTION_AMBIGUOUS,
                'multiple_rei_for_location',
                $requestId,
                $locationId,
                null,
                $reiIds,
                []
            );
        }

        if ($reiIds === []) {
            return $this->result(
                self::RESOLUTION_ORPHAN,
                'no_rei_for_location',
                $requestId,
                $locationId,
                null,
                [],
                []
            );
        }

        return $this->result(
            self::RESOLUTION_CERTAIN,
            'exact_one_rei_unique_location',
            $requestId,
            $locationId,
            $reiIds[0],
            $reiIds,
            []
        );
    }

    /**
     * @return array{certain:int,ambiguous:int,orphan:int,by_reason:array<string,int>}
     */
    public function summarize(string $connection = 'legacy'): array
    {
        $summary = [
            'certain' => 0,
            'ambiguous' => 0,
            'orphan' => 0,
            'by_reason' => [],
        ];

        DB::connection($connection)
            ->table('request')
            ->orderBy('idRequest')
            ->chunkById(500, function ($rows) use (&$summary, $connection) {
                foreach ($rows as $row) {
                    $result = $this->resolve($row, $connection);
                    $bucket = match ($result['resolution']) {
                        self::RESOLUTION_CERTAIN => 'certain',
                        self::RESOLUTION_AMBIGUOUS => 'ambiguous',
                        default => 'orphan',
                    };
                    $summary[$bucket]++;
                    $summary['by_reason'][$result['reason']] = ($summary['by_reason'][$result['reason']] ?? 0) + 1;
                }
            }, 'idRequest');

        return $summary;
    }

    /**
     * @param  list<int>  $reiIds
     * @param  list<int>  $siblingRequestIds
     * @return array{
     *   resolution: string,
     *   reason: string,
     *   request_id: int,
     *   location_id: int|null,
     *   rei_id: int|null,
     *   rei_ids: list<int>,
     *   sibling_request_ids: list<int>
     * }
     */
    private function result(
        string $resolution,
        string $reason,
        int $requestId,
        ?int $locationId,
        ?int $reiId,
        array $reiIds,
        array $siblingRequestIds
    ): array {
        return [
            'resolution' => $resolution,
            'reason' => $reason,
            'request_id' => $requestId,
            'location_id' => $locationId,
            'rei_id' => $reiId,
            'rei_ids' => $reiIds,
            'sibling_request_ids' => $siblingRequestIds,
        ];
    }
}
