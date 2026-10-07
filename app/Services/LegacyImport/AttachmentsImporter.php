<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\ValuationAttachment;
use App\Services\LegacyImport\Concerns\ImportsPropertyChildRows;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use Illuminate\Database\Query\Builder;
use stdClass;

/**
 * Legacy `attachment` metadata → valuation_attachments (disk=legacy; files stay in the old tree).
 */
class AttachmentsImporter extends ResumableImporter
{
    use ImportsPropertyChildRows;

    public static function key(): string
    {
        return 'attachments';
    }

    protected function legacyTable(): string
    {
        return 'attachment';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table($this->legacyTable());
    }

    protected function importRow(stdClass $row): void
    {
        $legacyId = (int) $row->id;
        $legacyRequestId = (int) ($row->req_id ?? 0);
        $requestId = $this->valuationRequestIdByLegacy($legacyRequestId);
        $file = $this->str($row->file ?? null);
        if ($requestId === null || $file === null) {
            $this->quarantine(static::key(), $legacyId, (array) $row, $requestId === null ? 'request_not_imported' : 'missing_file_name');

            return;
        }

        if ($this->dryRun) {
            $this->imported++;

            return;
        }

        ValuationAttachment::query()->updateOrCreate(
            ['legacy_key' => $this->legacyTable().':'.$legacyId],
            [
                'valuation_request_id' => $requestId,
                'type' => $this->str($row->type ?? null),
                'original_name' => $file,
                'path' => 'attachments/'.$legacyRequestId.'/'.$file,
                'disk' => 'legacy',
            ]
        );
        $this->imported++;
    }
}
