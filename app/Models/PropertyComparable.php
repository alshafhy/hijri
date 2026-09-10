<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyComparable extends Model
{
    protected $fillable = [
        'property_id',
        'sequence',
        'real_estate_type',
        'street_width',
        'front_number',
        'front_type',
        'area',
        'price',
        'percentage',
        'coordinates_url',
        'information_source',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'sequence' => 'integer',
            'street_width' => 'integer',
            'front_number' => 'integer',
            'area' => 'float',
            'price' => 'float',
            'percentage' => 'integer',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
