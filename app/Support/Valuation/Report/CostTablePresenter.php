<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy cost table (evaluationInfoTable) and final valuation price block built from the CostTableSection data,
 * with every cell formatted for printing.
 *
 * @phpstan-type Cost array{rows: list<array{title: string, area: ?float, price: ?float, total: ?float}>, profit: ?float, depreciation: ?float, forced_sale: ?array{percentage: int, amount: float}, movables: ?float, final_amount: ?float, final_in_words: string, manual: bool}
 * @phpstan-type Row array{label: string, area: string, price: string, total: string}
 */
final class CostTablePresenter
{
    private const string EMPTY_CELL = '-';

    /**
     * Null without cost data or when the evaluation table is hidden on the request.
     *
     * @param  Cost|null  $cost
     * @return array{rows: list<Row>, words: string, amount: string}|null
     */
    public function table(?array $cost, bool $hidden = false): ?array
    {
        if ($cost === null || $hidden) {
            return null;
        }

        return [
            'rows' => [...array_map($this->gridRow(...), $cost['rows']), ...$this->extraRows($cost)],
            'words' => $cost['final_in_words'],
            'amount' => $this->money($cost['final_amount']) ?? '—',
        ];
    }

    /**
     * Null without a final amount.
     *
     * @param  Cost|null  $cost
     * @return array{amount: string, words: string}|null
     */
    public function finalValue(?array $cost): ?array
    {
        $amount = $this->money($cost['final_amount'] ?? null);
        if ($cost === null || $amount === null) {
            return null;
        }

        return [
            'amount' => $amount.' '.Card::label('saudi_riyal'),
            'words' => trim($cost['final_in_words'].' '.Card::label('only')),
        ];
    }

    /**
     * @param  array{title: string, area: ?float, price: ?float, total: ?float}  $row
     * @return Row
     */
    private function gridRow(array $row): array
    {
        return [
            'label' => Card::text($row['title']) ?? '',
            'area' => Card::number($row['area']) ?? '',
            'price' => number_format($row['price'] ?? 0, 2),
            'total' => number_format($row['total'] ?? 0, 2),
        ];
    }

    /**
     * Profit, depreciation and movables print only when positive; the forced sale only under the forced sale assumption.
     *
     * @param  Cost  $cost
     * @return list<Row>
     */
    private function extraRows(array $cost): array
    {
        $rows = [];
        if (($cost['profit'] ?? 0) > 0) {
            $rows[] = $this->row(Card::label('developer_earnings').' (+)', self::EMPTY_CELL, self::EMPTY_CELL, (float) $cost['profit']);
        }
        if (($cost['depreciation'] ?? 0) > 0) {
            $rows[] = $this->row(Card::label('depreciation_amount').' (-)', self::EMPTY_CELL, self::EMPTY_CELL, (float) $cost['depreciation']);
        }
        if ($cost['forced_sale'] !== null) {
            $percentage = $cost['forced_sale']['percentage'];
            $rows[] = $this->row(Card::label('forced_sale')." ({$percentage}%) (-)", self::EMPTY_CELL, "%{$percentage}", $cost['forced_sale']['amount']);
        }
        if (($cost['movables'] ?? 0) > 0) {
            $movables = (float) $cost['movables'];
            $rows[] = $this->row(Card::label('movables'), number_format($movables, 2), self::EMPTY_CELL, $movables);
        }

        return $rows;
    }

    /**
     * @return Row
     */
    private function row(string $label, string $area, string $price, float $total): array
    {
        return ['label' => $label, 'area' => $area, 'price' => $price, 'total' => number_format($total, 2)];
    }

    private function money(?float $value): ?string
    {
        return $value !== null ? number_format($value, 2) : null;
    }
}
