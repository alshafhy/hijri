<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyBorder extends Model
{
    protected $fillable = [
        'legacy_id',
        'property_id',
        'north',
        'north_length',
        'east',
        'east_length',
        'south',
        'south_length',
        'west',
        'west_length',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'property_id' => 'integer',
            'north_length' => 'float',
            'east_length' => 'float',
            'south_length' => 'float',
            'west_length' => 'float',
        ];
    }

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
