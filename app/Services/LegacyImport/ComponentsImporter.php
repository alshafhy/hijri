<?php

declare(strict_types=1);

namespace App\Services\LegacyImport;

use App\Models\ImportRun;
use App\Models\Property;
use App\Services\LegacyImport\Concerns\ResumableImporter;
use App\Support\Valuation\ComponentKeyMap;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use stdClass;

final class ComponentsImporter extends ResumableImporter
{
    /** @var array<int, int> legacy REI id => property id */
    private array $propertyMap = [];

    /** @var array<int, stdClass> legacy REI id => price row */
    private array $priceMap = [];

    /** @var list<array<string, mixed>> */
    private array $buffer = [];

    public function __construct(
        ImportRun $run,
        bool $dryRun = false,
        bool $resume = false,
    ) {
        parent::__construct($run, $dryRun, $resume);
        if (! $dryRun) {
            $this->propertyMap = Property::query()->pluck('id', 'legacy_id')->map(fn ($id) => (int) $id)->all();
            foreach ($this->legacy()->table('building_price')->cursor() as $price) {
                $this->priceMap[(int) $price->Real_Estate_Information] = $price;
            }
        }
    }

    public static function key(): string
    {
        return 'components';
    }

    protected function legacyIdColumn(): string
    {
        return 'id';
    }

    protected function legacyQuery(): Builder
    {
        return $this->legacy()->table('building_areas');
    }

    public function import(): array
    {
        $result = parent::import();
        $this->flushBuffer();

        return $result;
    }

    protected function importRow(stdClass $row): void
    {
        $legacyReiId = (int) ($row->Real_Estate_Information ?? 0);
        $propertyId = $this->propertyMap[$legacyReiId] ?? null;
        if ($propertyId === null) {
            $this->quarantine(self::key(), (int) $row->id, ['rei' => $legacyReiId], 'property_not_imported');

            return;
        }

        $priceRow = $this->priceMap[$legacyReiId] ?? null;
        $now = now()->toDateTimeString();

        foreach (ComponentKeyMap::MAP as $areaCol => [$priceCol, $key]) {
            $areaRaw = $row->{$areaCol} ?? null;
            $priceRaw = $priceRow->{$priceCol} ?? null;
            if ($key === 'base_area' && $priceRow !== null) {
                $priceRaw = $priceRow->base_amount ?? $priceRaw;
            }

            $area = $this->toDecimal($areaRaw);
            $price = $this->toDecimal($priceRaw);
            if ($area === null && $price === null) {
                continue;
            }

            if ($this->dryRun) {
                $this->imported++;

                continue;
            }

            $this->buffer[] = [
                'property_id' => $propertyId,
                'component_key' => $key,
                'area_value' => $area,
                'price_value' => $price,
                'meta' => json_encode([
                    'legacy_area_column' => $areaCol,
                    'legacy_price_column' => $priceCol,
                ], JSON_THROW_ON_ERROR),
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $this->imported++;

            if (count($this->buffer) >= 500) {
                $this->flushBuffer();
            }
        }
    }

    private function flushBuffer(): void
    {
        if ($this->buffer === [] || $this->dryRun) {
            return;
        }

        DB::table('property_components')->upsert(
            $this->buffer,
            ['property_id', 'component_key'],
            ['area_value', 'price_value', 'meta', 'updated_at']
        );
        $this->buffer = [];
    }

    private function toDecimal(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (! is_numeric($value)) {
            return null;
        }

        return number_format((float) $value, 4, '.', '');
    }
}
