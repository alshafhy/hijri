<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyBuilding extends Model
{
    protected $fillable = [
        'legacy_key',
        'property_id',
        'kind',
        'floor_no',
        'build_age',
        'build_state',
        'architectural',
        'quality_insulation',
        'bishop_type',
        'structure_construction',
        'default_build_age',
        'remaining_build_age',
        'house_unit_no',
        'commercial_unit_no',
        'description',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'property_id' => 'integer',
            'floor_no' => 'integer',
            'default_build_age' => 'integer',
            'remaining_build_age' => 'integer',
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
