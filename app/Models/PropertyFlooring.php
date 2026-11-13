<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyFlooring extends Model
{
    protected $fillable = [
        'legacy_id',
        'property_id',
        'flooring_yards',
        'yards_description',
        'flooring_reception',
        'reception_description',
        'flooring_entrance',
        'entrance_description',
        'flooring_rooms',
        'rooms_description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'property_id' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Property, $this>
     */
    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
