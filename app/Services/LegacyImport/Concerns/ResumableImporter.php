<?php

declare(strict_types=1);

namespace App\Services\LegacyImport\Concerns;

use App\Models\ImportCheckpoint;
use App\Models\ImportQuarantine;
use App\Models\ImportRun;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;

abstract class ResumableImporter
{
    protected int $chunkSize;

    protected int $processed = 0;

    protected int $imported = 0;

    protected int $quarantined = 0;

    protected int $skipped = 0;

    public function __construct(
        protected ImportRun $run,
        protected bool $dryRun = false,
        protected bool $resume = false,
    ) {
        $this->chunkSize = max(1, (int) config('legacy_import.chunk_size', 200));
    }

    abstract public static function key(): string;

    abstract protected function legacyIdColumn(): string;

    abstract protected function legacyQuery(): Builder;

    abstract protected function importRow(stdClass $row): void;

    /**
     * @return array{processed:int,imported:int,quarantined:int,skipped:int,last_legacy_id:int}
     */
    public function import(): array
    {
        $checkpoint = $this->checkpoint();
        $afterId = $this->resume ? (int) $checkpoint->last_legacy_id : 0;
        if (! $this->resume) {
            $checkpoint->last_legacy_id = 0;
            $checkpoint->processed = 0;
            if (! $this->dryRun) {
                $checkpoint->save();
            }
        }

        $idColumn = $this->legacyIdColumn();
        $lastId = $afterId;

        $this->legacyQuery()
            ->when($afterId > 0, fn (Builder $q) => $q->where($idColumn, '>', $afterId))
            ->orderBy($idColumn)
            ->chunkById($this->chunkSize, function ($rows) use ($checkpoint, $idColumn, &$lastId): void {
                foreach ($rows as $row) {
                    $legacyId = (int) data_get($row, $idColumn);
                    try {
                        $this->importRow($row);
                    } catch (\Throwable $e) {
                        Log::warning('legacy_import.row_failed', [
                            'importer' => static::key(),
                            'legacy_id' => $legacyId,
                            'message' => $e->getMessage(),
                        ]);
                        $this->quarantine(
                            static::key(),
                            $legacyId,
                            $this->safePayload((array) $row),
                            'exception: '.$e->getMessage()
                        );
                    }

                    $this->processed++;
                    $lastId = max($lastId, $legacyId);
                }

                $checkpoint->last_legacy_id = $lastId;
                $checkpoint->processed = (int) $checkpoint->processed + $rows->count();
                if (! $this->dryRun) {
                    $checkpoint->save();
                }
            }, $idColumn);

        return [
            'processed' => $this->processed,
            'imported' => $this->imported,
            'quarantined' => $this->quarantined,
            'skipped' => $this->skipped,
            'last_legacy_id' => $lastId,
        ];
    }

    protected function legacy(): ConnectionInterface
    {
        return DB::connection((string) config('legacy_import.legacy_connection', 'legacy'));
    }

    protected function checkpoint(): ImportCheckpoint
    {
        if ($this->dryRun) {
            return new ImportCheckpoint([
                'import_run_id' => $this->run->id,
                'importer' => static::key(),
                'last_legacy_id' => 0,
                'processed' => 0,
            ]);
        }

        return ImportCheckpoint::query()->firstOrCreate(
            [
                'import_run_id' => $this->run->id,
                'importer' => static::key(),
            ],
            [
                'last_legacy_id' => 0,
                'processed' => 0,
            ]
        );
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    protected function quarantine(string $entityType, ?int $legacyId, ?array $payload, string $reason): void
    {
        $this->quarantined++;

        if ($this->dryRun) {
            return;
        }

        ImportQuarantine::query()->create([
            'import_run_id' => $this->run->id,
            'entity_type' => $entityType,
            'legacy_id' => $legacyId,
            'payload' => $payload,
            'reason' => mb_substr($reason, 0, 250),
        ]);
    }

    /**
     * Idempotent upsert by legacy_id.
     *
     * @param  class-string<Model>  $modelClass
     * @param  array<string, mixed>  $attributes
     */
    protected function upsertByLegacyId(string $modelClass, int $legacyId, array $attributes): ?Model
    {
        if ($this->dryRun) {
            $this->imported++;

            return null;
        }

        /** @var Model $model */
        $model = $modelClass::query()->updateOrCreate(
            ['legacy_id' => $legacyId],
            $attributes
        );

        $this->imported++;

        return $model;
    }

    protected function attr(stdClass $row, string $key, mixed $default = null): mixed
    {
        return property_exists($row, $key) ? $row->{$key} : $default;
    }

    /**
     * Read a column that may use Arabic tatweel (ـ) in the legacy schema.
     */
    protected function attrTatweel(stdClass $row, string $asciiName): mixed
    {
        $tatweel = str_replace('_', 'ـ', $asciiName);
        if (property_exists($row, $tatweel)) {
            return $row->{$tatweel};
        }
        if (property_exists($row, $asciiName)) {
            return $row->{$asciiName};
        }

        foreach ((array) $row as $key => $value) {
            $normalized = str_replace(['ـ', ' '], ['_', ''], (string) $key);
            if ($normalized === $asciiName) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function safePayload(array $payload): array
    {
        foreach (['password', 'remember_token', 'password_confirmation'] as $secret) {
            unset($payload[$secret]);
        }

        return $payload;
    }
}
