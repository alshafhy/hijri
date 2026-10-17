<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Actions\Valuation\ResolveValuationFinalAmountAction;
use App\Models\Property;
use App\Models\PropertyTotal;
use App\Models\ValuationRequest;
use App\Support\Valuation\ArabicAmountInWords;
use App\Support\Valuation\EnglishAmountInWords;
use App\Support\Valuation\ValuationBreakdown;
use App\Support\Valuation\ValuationFieldGrid;

/**
 * Cost approach table (grid rows, adjustments, final amount + words) for valuation reports.
 */
final class CostTableSection
{
    private const FORCED_SALE_ASSUMPTION = 4;

    public function __construct(
        private readonly ResolveValuationFinalAmountAction $resolveFinalAmount = new ResolveValuationFinalAmountAction,
        private readonly ArabicAmountInWords $arabicWords = new ArabicAmountInWords,
        private readonly EnglishAmountInWords $englishWords = new EnglishAmountInWords,
    ) {}

    /**
     * @return array{rows: list<array{title: string, area: ?float, price: ?float, total: ?float}>, profit: ?float, depreciation: ?float, forced_sale: ?array{percentage: int, amount: float}, movables: ?float, final_amount: ?float, final_in_words: string, manual: bool}
     */
    public function build(Property $property, ?string $locale = null): array
    {
        $totals = $this->totals($property);
        $total = $totals['property_total'];

        return [
            'rows' => $this->rows($property),
            'profit' => $total?->profit_amount,
            'depreciation' => $total?->depreciation_amount,
            'forced_sale' => $this->forcedSale($property, $total),
            'movables' => $total?->movables,
            'final_amount' => $totals['amount'],
            'final_in_words' => $this->inWords($totals['amount'], $locale ?? app()->getLocale()),
            'manual' => $totals['is_manual'],
        ];
    }

    /**
     * @return array{amount: float|null, is_manual: bool, calculated_amount: float|null, manual_amount: float|null, forced_sale_percentage: int|null, forced_sale_amount: float|null, total_area: float|null, property_total: PropertyTotal|null}
     */
    public function totals(ValuationRequest|Property $source): array
    {
        return $this->resolveFinalAmount->execute($source);
    }

    public function inWords(?float $amount, string $locale): string
    {
        if ($amount === null) {
            return '';
        }

        $converter = $locale === 'en' ? $this->englishWords : $this->arabicWords;

        return trim($converter->convert($amount).' '.__('Saudi riyal', [], $locale));
    }

    /**
     * @return list<array{title: string, area: ?float, price: ?float, total: ?float}>
     */
    private function rows(Property $property): array
    {
        $gridKeys = array_column(ValuationFieldGrid::rows((string) $property->property_kind, (string) $property->property_type), 'key');
        $rows = array_filter(
            ValuationBreakdown::forProperty($property)['rows'],
            static fn (array $row): bool => in_array($row['key'], $gridKeys, true),
        );

        return array_values(array_map(
            static fn (array $row): array => [
                'title' => $row['title'],
                'area' => $row['area'],
                'price' => $row['price'],
                'total' => $row['total'],
            ],
            $rows,
        ));
    }

    /**
     * @return ?array{percentage: int, amount: float}
     */
    private function forcedSale(Property $property, ?PropertyTotal $total): ?array
    {
        if ((int) $property->value_assumption !== self::FORCED_SALE_ASSUMPTION) {
            return null;
        }

        if ($total?->forced_sale_percentage === null || $total->forced_sale_amount === null) {
            return null;
        }

        return [
            'percentage' => $total->forced_sale_percentage,
            'amount' => $total->forced_sale_amount,
        ];
    }
}
