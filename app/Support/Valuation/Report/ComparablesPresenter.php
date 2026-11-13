<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Support\Valuation\Report\Cards\Card;

/**
 * Market approach pages of the full report (comparables table, comparables map, adjustments matrix with the meter price rows)
 * built from the ComparablesSection data, with every cell formatted for printing.
 *
 * @phpstan-type Comparable array{sequence: int, type: ?string, front_type: ?string, area: ?float, price_per_meter: ?float, total: ?float, coordinates_url: ?string}
 * @phpstan-type Row array{key: string, label: string, values: list<?string>, percentages: list<?string>}
 * @phpstan-type Settlement array{final_meter_prices: list<?float>, weights: list<?float>, weighted_meter_price: ?float, approximate_meter_price: ?float}
 * @phpstan-type Section array{comparables: list<Comparable>, adjustments: array{columns: list<int>, rows: list<Row>}, settlement: Settlement, information_source: ?string, map: ?string, hidden: bool}
 */
final class ComparablesPresenter
{
    private const array ORDINALS = [
        1 => 'The first comparator',
        2 => 'The second comparator',
        3 => 'The third comparator',
        4 => 'The fourth comparator',
    ];

    /**
     * Null when the comparisons are hidden or there is nothing to compare, which skips the whole block.
     *
     * @param  Section|null  $section
     * @return array{comparables: list<array{label: string, type: ?string, front_type: ?string, area: ?string, price_per_meter: ?string, total: ?string, coordinates_label: string, coordinates_url: ?string}>, information_source: ?string, map: ?string, columns: list<string>, span: int, rows: list<array{key: string, label: string, values: list<?string>, percentages: list<?string>, merged: bool}>, settlement: array{weighted_meter_price: string, approximate_meter_price: string}}|null
     */
    public function build(?array $section): ?array
    {
        if ($section === null || $section['hidden'] || $section['comparables'] === []) {
            return null;
        }

        $columns = $section['adjustments']['columns'];

        return [
            'comparables' => array_map($this->comparable(...), $section['comparables']),
            'information_source' => Card::text($section['information_source']),
            'map' => $section['map'],
            'columns' => array_map(fn (int $column): string => $this->comparatorTitle($column), $columns),
            'span' => 1 + 2 * count($columns),
            'rows' => array_map($this->row(...), $section['adjustments']['rows']),
            'settlement' => $this->settlement($section['settlement']),
        ];
    }

    /**
     * @param  Comparable  $comparable
     * @return array{label: string, type: ?string, front_type: ?string, area: ?string, price_per_meter: ?string, total: ?string, coordinates_label: string, coordinates_url: ?string}
     */
    private function comparable(array $comparable): array
    {
        return [
            'label' => Card::label('comparative').' '.$comparable['sequence'],
            'type' => Card::text($comparable['type']),
            'front_type' => Card::text($comparable['front_type']),
            'area' => Card::number($comparable['area']),
            'price_per_meter' => $this->money($comparable['price_per_meter']),
            'total' => $this->money($comparable['total']),
            'coordinates_label' => Card::label('comparative_coordinates').' '.$comparable['sequence'],
            'coordinates_url' => $comparable['coordinates_url'],
        ];
    }

    /**
     * Rows without a settlement ratio field (subtotals, prices, weights) print one merged cell per comparable;
     * criterion rows always print value + ratio.
     *
     * @param  Row  $row
     * @return array{key: string, label: string, values: list<?string>, percentages: list<?string>, merged: bool}
     */
    private function row(array $row): array
    {
        return $row + ['merged' => ! ComparablesSection::hasRatio($row['key'])];
    }

    /**
     * @param  Settlement  $settlement
     * @return array{weighted_meter_price: string, approximate_meter_price: string}
     */
    private function settlement(array $settlement): array
    {
        return [
            'weighted_meter_price' => $this->money($settlement['weighted_meter_price']) ?? '—',
            'approximate_meter_price' => $this->money($settlement['approximate_meter_price']) ?? '—',
        ];
    }

    private function comparatorTitle(int $column): string
    {
        return isset(self::ORDINALS[$column])
            ? Card::label(self::ORDINALS[$column])
            : Card::label('comparative').' '.$column;
    }

    private function money(?float $value): ?string
    {
        return $value !== null ? number_format($value, 2) : null;
    }
}
