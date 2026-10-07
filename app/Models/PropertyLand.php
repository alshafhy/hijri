<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyLand extends Model
{
    protected $fillable = [
        'legacy_id',
        'property_id',
        'land_nature',
        'facade',
        'site',
        'door_ex',
        'street',
        'door_in',
        'neighbor',
        'include',
        'heater_no',
        'lift_no',
        'bathroom_a_no',
        'bathroom_e_no',
        'electricity_no',
        'electricity_num',
        'water_no',
        'water_num',
        'ownership_type',
        'furnishing_status',
        'building_type',
        'finishing_status',
        'street_width',
        'total_building_area',
        'additional_features',
        'approved_floors',
        'property_use',
        'approved_building',
        'sector_description',
        'ownership_description',
        'market_value',
        'market_value_description',
        'surrounding_facilities',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'property_id' => 'integer',
            'ownership_type' => 'integer',
            'furnishing_status' => 'integer',
            'building_type' => 'integer',
            'finishing_status' => 'integer',
            'street_width' => 'float',
            'total_building_area' => 'float',
            'approved_floors' => 'float',
            'approved_building' => 'float',
            'market_value' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
