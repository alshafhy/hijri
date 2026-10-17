<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Support\Valuation\ComparablesCalculator;
use App\Support\Valuation\StaticMapImage;
use DateTimeInterface;
use Illuminate\Support\Collection;

/**
 * Market approach for valuation reports: comparables table, one generic adjustments matrix
 * (1..4 columns), settlement and the comparables map.
 *
 * @phpstan-type CalculatedRow array{total: float, first_value: float, first_price: float, second_value: float, final_price: float}
 */
final class ComparablesSection
{
    private const string NUMBER = '-?\d+(?:\.\d+)?';

    /** row key => [pdf label key, value spec, percentage field|null]; value spec is a column, `option:list:column` or a computed `@name`. */
    private const array ROWS = [
        'transaction_date' => ['transaction date', '@transaction_date', 'settlement_ratio'],
        'market_conditions' => ['market conditions value', 'market_conditions_value', 'market_conditions_percentage'],
        'financing_terms' => ['financing terms value', 'financing_terms_value', 'financing_terms_percentage'],
        'sale_terms' => ['sale terms value', 'sale_terms_value', 'sale_terms_percentage'],
        'transaction_subtotal' => ['Total settlement percentages', '@transaction_subtotal', null],
        'previous_settlement_value' => ['The value of previous settlement percentages', '@previous_settlement_value', null],
        'price_after_transaction' => ['Price after adjustments', '@price_after_transaction', null],
        'area' => ['space', '@area', 'area_percentage'],
        'location_status' => ['site attractiveness', 'option:location_status_value:location_status_value', 'location_status_percentage'],
        'easy_access' => ['easy access', 'option:easy_access_value:easy_access_value', 'easy_access_percentage'],
        'main_street_width' => ['main street width', 'main_street_width_value', 'main_street_width_percentage'],
        'streets_count' => ['Number of streets/facades', 'streets_count_value', 'streets_count_percentage'],
        'land_shape' => ['land shape', 'option:land_shape_value:land_shape_value', 'land_shape_percentage'],
        'land_topography' => ['land topography', 'land_topography_value', 'land_topography_percentage'],
        'natural_factors' => ['natural factors', 'natural_factors_value', 'natural_factors_percentage'],
        'near_main_street' => ['near main street', 'option:near_main_street_value:near_main_street_value', 'near_main_street_percentage'],
        'other' => ['other', 'other_value', 'other_percentage'],
        'property_total' => ['last total adjustments', '@property_total', null],
        'total_adjustments_value' => ['total adjustments value', '@total_adjustments_value', null],
        'total_adjustments_price' => ['Price after adjustments', '@total_adjustments_price', null],
        'proportional_adjustment' => ['proportional adjustment', '@proportional_adjustment', null],
    ];

    private const array TRANSACTION_FIELDS = ['market_conditions_percentage', 'financing_terms_percentage', 'sale_terms_percentage'];

    public function __construct(
        private readonly StaticMapImage $map = new StaticMapImage,
        private readonly ReportLabels $labels = new ReportLabels,
        private readonly ComparablesCalculator $calculator = new ComparablesCalculator,
    ) {}

    /**
     * @return array{comparables: list<array{sequence: int, type: ?string, front_type: ?string, street_width: ?string, area: ?float, price_per_meter: ?float, total: ?float, percentage: ?float, coordinates_url: ?string, transaction_date: ?string}>, adjustments: array{columns: list<int>, rows: list<array{key: string, label: string, values: list<?string>, percentages: list<?string>}>}, settlement: array{final_meter_prices: list<?float>, weights: list<?float>, weighted_meter_price: ?float, approximate_meter_price: ?float}, information_source: ?string, map: ?string, hidden: bool}
     */
    public function build(Property $property): array
    {
        $comparables = $property->comparables->sortBy('sequence')->keyBy('sequence');
        $adjustments = $property->adjustments->sortBy('type')->keyBy('type');
        $hidden = (bool) $property->total?->hide_comparisons_info_table;
        $calculated = $this->calculate($comparables, $adjustments);

        return [
            'comparables' => $this->comparables($comparables, $adjustments),
            'adjustments' => [
                'columns' => array_map('intval', $adjustments->keys()->all()),
                'rows' => $this->adjustmentRows($comparables, $adjustments, $calculated),
            ],
            'settlement' => $this->settlement($adjustments, $calculated),
            'information_source' => $this->informationSource($comparables, $property),
            'map' => $hidden ? null : $this->map->comparablesDataUri($this->subject($property), $this->mapPoints($comparables)),
            'hidden' => $hidden,
        ];
    }

    /**
     * @param  Collection<int, PropertyComparable>  $comparables
     * @param  Collection<int, PropertyAdjustment>  $adjustments
     * @return list<array{sequence: int, type: ?string, front_type: ?string, street_width: ?string, area: ?float, price_per_meter: ?float, total: ?float, percentage: ?float, coordinates_url: ?string, transaction_date: ?string}>
     */
    private function comparables(Collection $comparables, Collection $adjustments): array
    {
        return $comparables->map(fn (PropertyComparable $c): array => [
            'sequence' => $c->sequence,
            'type' => $this->text($c->real_estate_type),
            'front_type' => $this->text($c->front_type),
            'street_width' => $this->text($c->street_width),
            'area' => $c->area,
            'price_per_meter' => $c->price,
            'total' => $c->area !== null && $c->price !== null ? $c->area * $c->price : null,
            'percentage' => $c->percentage !== null ? (float) $c->percentage : null,
            'coordinates_url' => $this->text($c->coordinates_url),
            'transaction_date' => $this->transactionDate($adjustments->get($c->sequence)),
        ])->values()->all();
    }

    /**
     * @param  Collection<int, PropertyComparable>  $comparables
     * @param  Collection<int, PropertyAdjustment>  $adjustments
     * @param  array<int, CalculatedRow>  $calculated
     * @return list<array{key: string, label: string, values: list<?string>, percentages: list<?string>}>
     */
    private function adjustmentRows(Collection $comparables, Collection $adjustments, array $calculated): array
    {
        $rows = [];
        foreach (self::ROWS as $key => [$labelKey, $valueSpec, $percentageField]) {
            $values = $percentages = [];
            foreach ($adjustments as $sequence => $adjustment) {
                $values[] = $this->cell($valueSpec, $adjustment, $comparables->get($sequence), $calculated[$sequence] ?? null);
                $percentages[] = $percentageField !== null ? $this->percent($adjustment->getAttribute($percentageField)) : null;
            }
            $rows[] = ['key' => $key, 'label' => $this->label($labelKey), 'values' => $values, 'percentages' => $percentages];
        }

        return $rows;
    }

    /**
     * @param  CalculatedRow|null  $calculated
     */
    private function cell(string $spec, PropertyAdjustment $adjustment, ?PropertyComparable $comparable, ?array $calculated): ?string
    {
        if (str_starts_with($spec, 'option:')) {
            [, $list, $field] = explode(':', $spec);

            return $this->labels->option($list, $adjustment->getAttribute($field));
        }

        return match ($spec) {
            '@transaction_date' => $this->transactionDate($adjustment),
            '@transaction_subtotal' => $this->percent($this->sum($adjustment, self::TRANSACTION_FIELDS)),
            '@previous_settlement_value' => $this->money($calculated['first_value'] ?? null),
            '@price_after_transaction' => $this->money($calculated['first_price'] ?? null),
            '@area' => $this->money($comparable?->area),
            '@property_total' => $this->percent($this->sum($adjustment, ComparablesCalculator::PROPERTY_PERCENTAGES)),
            '@total_adjustments_value' => $this->money($adjustment->total_adjustments_value ?? $this->totalAdjustmentsValue($calculated)),
            '@total_adjustments_price' => $this->money($adjustment->total_adjustments_price ?? $calculated['final_price'] ?? null),
            '@proportional_adjustment' => $this->percent($adjustment->proportional_adjustment),
            default => $this->text($adjustment->getAttribute($spec)),
        };
    }

    /**
     * @param  CalculatedRow|null  $calculated
     */
    private function totalAdjustmentsValue(?array $calculated): ?float
    {
        return $calculated !== null ? $calculated['first_value'] + $calculated['second_value'] : null;
    }

    /**
     * @param  Collection<int, PropertyComparable>  $comparables
     * @param  Collection<int, PropertyAdjustment>  $adjustments
     * @return array<int, CalculatedRow>
     */
    private function calculate(Collection $comparables, Collection $adjustments): array
    {
        $input = [];
        foreach ($adjustments as $sequence => $adjustment) {
            $comparable = $comparables->get($sequence);
            $input[(int) $sequence] = $adjustment->attributesToArray() + ['area' => $comparable?->area, 'price' => $comparable?->price];
        }

        return $this->calculator->calculate($input)['rows'];
    }

    /**
     * @param  Collection<int, PropertyAdjustment>  $adjustments
     * @param  array<int, CalculatedRow>  $calculated
     * @return array{final_meter_prices: list<?float>, weights: list<?float>, weighted_meter_price: ?float, approximate_meter_price: ?float}
     */
    private function settlement(Collection $adjustments, array $calculated): array
    {
        $first = $adjustments->first();

        return [
            'final_meter_prices' => array_values(array_map(fn (array $row): float => round($row['final_price'], 2), $calculated)),
            'weights' => $adjustments->map(fn (PropertyAdjustment $a): ?float => $a->proportional_adjustment !== null ? (float) $a->proportional_adjustment : null)->values()->all(),
            'weighted_meter_price' => $first?->meter_price,
            'approximate_meter_price' => $first?->meter_price_approximately,
        ];
    }

    /**
     * @param  Collection<int, PropertyComparable>  $comparables
     */
    private function informationSource(Collection $comparables, Property $property): ?string
    {
        $source = $comparables->map(fn (PropertyComparable $c): ?string => $this->text($c->information_source))->filter()->first();

        return $source ?? $this->text($property->informations_source);
    }

    /**
     * @return array{lat: ?float, lng: ?float}
     */
    private function subject(Property $property): array
    {
        $location = $property->location;

        return [
            'lat' => is_numeric($location?->x_axis) ? (float) $location->x_axis : null,
            'lng' => is_numeric($location?->y_axis) ? (float) $location->y_axis : null,
        ];
    }

    /**
     * @param  Collection<int, PropertyComparable>  $comparables
     * @return list<array{label: string, lat: float, lng: float}>
     */
    private function mapPoints(Collection $comparables): array
    {
        $points = [];
        foreach ($comparables as $comparable) {
            $coordinates = $this->parseCoordinates((string) $comparable->coordinates_url);
            if ($coordinates !== null) {
                $points[] = ['label' => (string) $comparable->sequence, 'lat' => $coordinates[0], 'lng' => $coordinates[1]];
            }
        }

        return $points;
    }

    /**
     * Extracts [lat, lng] from a maps URL or a plain "lat,lng" string; short links are not resolved.
     *
     * @return array{0: float, 1: float}|null
     */
    public function parseCoordinates(string $url): ?array
    {
        $url = trim(urldecode($url));
        if ($url === '' || preg_match('~(?:goo\.gl|maps\.app)/~i', $url) === 1) {
            return null;
        }

        $pair = '('.self::NUMBER.')\s*,\s*('.self::NUMBER.')';
        foreach (["~@{$pair}~", "~[?&](?:q|ll)={$pair}~", "~^{$pair}$~"] as $pattern) {
            if (preg_match($pattern, $url, $match) !== 1) {
                continue;
            }
            [$lat, $lng] = [(float) $match[1], (float) $match[2]];
            if (abs($lat) <= 90 && abs($lng) <= 180) {
                return [$lat, $lng];
            }
        }

        return null;
    }

    private function transactionDate(?PropertyAdjustment $adjustment): ?string
    {
        if ($adjustment === null) {
            return null;
        }

        $date = $adjustment->getAttribute('transaction_date');

        return $date instanceof DateTimeInterface ? $date->format('Y-m-d') : $this->text($adjustment->transaction_gregorian_date);
    }

    /**
     * @param  list<string>  $fields
     */
    private function sum(PropertyAdjustment $adjustment, array $fields): float
    {
        return array_sum(array_map(fn (string $field): float => (float) $adjustment->getAttribute($field), $fields));
    }

    private function label(string $key): string
    {
        $label = __("pdf.{$key}");

        return is_string($label) ? $label : $key;
    }

    private function percent(mixed $value): string
    {
        $number = is_numeric($value) ? (float) $value : 0.0;

        return rtrim(rtrim(number_format($number, 2, '.', ''), '0'), '.').'%';
    }

    private function money(?float $value): ?string
    {
        return $value !== null ? number_format($value, 2) : null;
    }

    private function text(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);

        return $text === '' ? null : $text;
    }
}
