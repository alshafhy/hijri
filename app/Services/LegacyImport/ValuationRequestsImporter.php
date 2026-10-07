<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\Company;
use App\Models\ValuationRequest;
use App\Services\LegacyImport\Concerns\ResolvesImportedEntities;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

final class ValuationRequestsImporter extends ResumableImporter
{
    use ResolvesImportedEntities;

    public static function key(): string
    {
        return 'valuation_requests';
    }

    protected function legacyIdColumn(): string
    {
        return 'idRequest';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('request');
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->idRequest;
        $companyLegacyId = isset($row->company_id) ? (int) $row->company_id : 0;
        $companyId = $companyLegacyId > 0
            ? Company::query()->where('legacy_id', $companyLegacyId)->value('id')
            : null;

        $coordinatorLegacy = isset($row->user) ? (int) $row->user : null;
        $evaluatorLegacy = isset($row->evaluative) ? (int) $row->evaluative : null;
        $subLegacy = isset($row->subUser) ? (int) $row->subUser : null;
        $fellowLegacy = isset($row->fellow_id) ? (int) $row->fellow_id : (isset($row->fellow) ? (int) $row->fellow : null);

        $deposit = $this->nullableString($row->deposit_number ?? null);

        $this->upsertByLegacyId(ValuationRequest::class, $legacyId, [
            'reference' => isset($row->reference) ? (int) $row->reference : null,
            'number' => $this->nullableString($row->number ?? null),
            'deposit_number' => $deposit,
            'company_id' => $companyId,
            'coordinator_user_id' => $this->mappedUserId($coordinatorLegacy),
            'evaluator_user_id' => $this->mappedUserId($evaluatorLegacy),
            'sub_user_id' => $this->mappedUserId($subLegacy),
            'fellow_user_id' => $this->mappedUserId($fellowLegacy),
            'legacy_coordinator_id' => $coordinatorLegacy ?: null,
            'legacy_evaluator_id' => $evaluatorLegacy ?: null,
            'legacy_sub_user_id' => $subLegacy ?: null,
            'legacy_fellow_id' => $fellowLegacy ?: null,
            'legacy_location_id' => isset($row->Location) ? (int) $row->Location : null,
            'state' => $this->nullableString($row->state ?? null),
            'approve' => isset($row->approve) ? (int) $row->approve : null,
            'started_at' => $row->start_date ?? null,
            'under_evaluation_at' => $row->under_evaluative ?? null,
            'evaluated_at' => $row->done_evaluative ?? null,
            'ended_at' => $row->end_date ?? null,
            'uploaded_on_qima' => (bool) ($row->uploaded_on_qima ?? false),
            'official_report_path' => $this->nullableString($row->official_report ?? null),
            'qima_locked_at' => ! empty($row->uploaded_on_qima) ? ($row->done_evaluative ?? $row->end_date ?? null) : null,
        ]);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $s = trim((string) $value);

        return $s === '' ? null : $s;
    }
}
