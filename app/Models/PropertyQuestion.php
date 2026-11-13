<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyQuestion extends Model
{
    protected $fillable = [
        'legacy_id',
        'property_id',
        'populated',
        'identical',
        'contrary',
        'concrete_roof',
        'ready',
        'ground_extension',
        'need_cost',
        'cost',
        'inside_inspection',
        'outside_inspection',
        'best_use',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'property_id' => 'integer',
            'best_use' => 'integer',
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
