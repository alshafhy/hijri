<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyAdjustment extends Model
{
    protected $fillable = [
        'legacy_id',
        'property_id',
        'type',
        'transaction_date',
        'market_conditions_value',
        'market_conditions_percentage',
        'financing_terms_value',
        'financing_terms_percentage',
        'sale_terms_value',
        'sale_terms_percentage',
        'area_percentage',
        'location_status_value',
        'location_status_percentage',
        'easy_access_value',
        'easy_access_percentage',
        'main_street_width_value',
        'main_street_width_percentage',
        'streets_count_value',
        'streets_count_percentage',
        'land_shape_value',
        'land_shape_percentage',
        'land_topography_value',
        'land_topography_percentage',
        'natural_factors_value',
        'natural_factors_percentage',
        'near_main_street_value',
        'near_main_street_percentage',
        'other_value',
        'other_percentage',
        'total_adjustments_value',
        'total_adjustments_price',
        'meter_price',
        'meter_price_approximately',
        'proportional_adjustment',
        'settlement_ratio',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'property_id' => 'integer',
            'type' => 'integer',
            'transaction_date' => 'date',
            'area_percentage' => 'integer',
            'location_status_value' => 'integer',
            'location_status_percentage' => 'integer',
            'easy_access_value' => 'integer',
            'easy_access_percentage' => 'integer',
            'main_street_width_value' => 'integer',
            'main_street_width_percentage' => 'integer',
            'streets_count_value' => 'integer',
            'streets_count_percentage' => 'integer',
            'land_shape_value' => 'integer',
            'land_shape_percentage' => 'integer',
            'land_topography_percentage' => 'integer',
            'natural_factors_percentage' => 'integer',
            'near_main_street_value' => 'integer',
            'near_main_street_percentage' => 'integer',
            'other_percentage' => 'integer',
            'total_adjustments_value' => 'float',
            'total_adjustments_price' => 'float',
            'meter_price' => 'float',
            'meter_price_approximately' => 'float',
            'proportional_adjustment' => 'integer',
            'settlement_ratio' => 'float',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
