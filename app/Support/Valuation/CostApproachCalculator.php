<?php

declare(strict_types=1);

namespace App\Support\Valuation;

/**
 * Server-side mirror of the legacy calculate_total() cost approach (SYSTEM_SPEC §7.8).
 */
final class CostApproachCalculator
{
    public const FORCED_SALE_ASSUMPTION = 4;

    /**
     * @param  array<string, array{area?: mixed, price?: mixed}>  $rows  keyed by component key
     * @param  array{
     *     profit_percentage?: mixed,
     *     depreciation_type?: mixed,
     *     build_age?: mixed,
     *     default_build_age?: mixed,
     *     remaining_build_age?: mixed,
     *     movables?: mixed,
     *     value_assumption?: mixed,
     *     forced_sale_percentage?: mixed
     * }  $options
     * @return array{
     *     total: float, land: float, total_area: float, profit_amount: float,
     *     depreciation_amount: float, calc_amount: float, forced_sale_amount: float,
     *     movables: float, total_amount: float
     * }
     */
    public function calculate(array $rows, array $options): array
    {
        $total = 0.0;
        $totalArea = 0.0;
        $rowTotals = [];
        foreach ($rows as $key => $row) {
            $area = (float) ($row['area'] ?? 0);
            $price = (float) ($row['price'] ?? 0);
            $rowTotals[$key] = $area * $price;
            $total += $area * $price;
            $totalArea += $area;
        }

        $land = 0.0;
        foreach (ValuationFieldGrid::LAND_KEYS as $landKey) {
            if (! empty($rowTotals[$landKey])) {
                $land = $rowTotals[$landKey];
                break;
            }
        }

        $profitPct = (float) ($options['profit_percentage'] ?? 0);
        $buildingValue = $total - $land;
        $profit = $buildingValue * $profitPct / 100;

        $defaultAge = (float) ($options['default_build_age'] ?? 0);
        $buildAge = is_numeric($options['build_age'] ?? null) ? (float) $options['build_age'] : 0.0;
        $remaining = (float) ($options['remaining_build_age'] ?? 0);
        $depreciation = 0.0;
        if ($defaultAge > 0) {
            $base = $buildingValue + $profit;
            $depreciation = match ((int) ($options['depreciation_type'] ?? 0)) {
                1 => ($buildAge / $defaultAge) * $base,
                2 => (($defaultAge - $remaining) / $defaultAge) * $base,
                default => 0.0,
            };
        }
        $depreciation = (float) round($depreciation);

        $movables = (float) ($options['movables'] ?? 0);
        $totalAmount = $total + $profit - $depreciation + $movables;

        $forced = 0.0;
        if ((int) ($options['value_assumption'] ?? 0) === self::FORCED_SALE_ASSUMPTION) {
            $forced = (float) round($total * (float) ($options['forced_sale_percentage'] ?? 0) / 100);
            $totalAmount -= $forced;
        }

        return [
            'total' => $total,
            'land' => $land,
            'total_area' => $totalArea,
            'profit_amount' => $profit,
            'depreciation_amount' => $depreciation,
            'calc_amount' => $total,
            'forced_sale_amount' => $forced,
            'movables' => $movables,
            'total_amount' => $totalAmount,
        ];
    }
}
