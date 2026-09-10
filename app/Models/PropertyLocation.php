<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyLocation extends Model
{
    protected $fillable = [
        'property_id',
        'legacy_location_id',
        'city_id',
        'neighborhood_id',
        'sketch_name',
        'street',
        'sketch_no',
        'part_no',
        'piece_number',
        'x_axis',
        'y_axis',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'legacy_location_id' => 'integer',
            'city_id' => 'integer',
            'neighborhood_id' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(GeoCity::class, 'city_id');
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(GeoNeighborhood::class, 'neighborhood_id');
    }
}
