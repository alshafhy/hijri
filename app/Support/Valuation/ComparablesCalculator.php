<?php

declare(strict_types=1);

namespace App\Support\Valuation;

/**
 * Market approach: two-stage adjustments over up to 4 comparables (SYSTEM_SPEC §7.8).
 */
final class ComparablesCalculator
{
    public const TRANSACTION_PERCENTAGES = [
        'market_conditions_percentage',
        'financing_terms_percentage',
        'sale_terms_percentage',
        'settlement_ratio',
    ];

    public const PROPERTY_PERCENTAGES = [
        'area_percentage',
        'location_status_percentage',
        'easy_access_percentage',
        'main_street_width_percentage',
        'streets_count_percentage',
        'land_shape_percentage',
        'land_topography_percentage',
        'natural_factors_percentage',
        'near_main_street_percentage',
        'other_percentage',
    ];

    /**
     * @param  array<int, array<string, mixed>>  $comparables  keyed by sequence 1..4
     * @return array{rows: array<int, array{total: float, first_value: float, first_price: float, second_value: float, final_price: float}>, meter_price: float}
     */
    public function calculate(array $comparables): array
    {
        $rows = [];
        $meterPrice = 0.0;

        foreach ($comparables as $sequence => $c) {
            $area = (float) ($c['area'] ?? 0);
            $price = (float) ($c['price'] ?? 0);

            $firstPct = 0.0;
            foreach (self::TRANSACTION_PERCENTAGES as $field) {
                $firstPct += (float) ($c[$field] ?? 0);
            }
            $firstValue = $firstPct * $price / 100;
            $firstPrice = $price + $firstValue;

            $secondPct = 0.0;
            foreach (self::PROPERTY_PERCENTAGES as $field) {
                $secondPct += (float) ($c[$field] ?? 0);
            }
            $secondValue = $secondPct * $firstPrice / 100;
            $finalPrice = $firstPrice + $secondValue;

            $rows[(int) $sequence] = [
                'total' => $area * $price,
                'first_value' => $firstValue,
                'first_price' => $firstPrice,
                'second_value' => $secondValue,
                'final_price' => $finalPrice,
            ];

            $meterPrice += $finalPrice * (float) ($c['proportional_adjustment'] ?? 0) / 100;
        }

        return ['rows' => $rows, 'meter_price' => round($meterPrice, 2)];
    }
}
