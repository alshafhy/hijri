<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeoNeighborhood extends Model
{
    protected $table = 'geo_neighborhoods';

    protected $fillable = [
        'legacy_id',
        'city_id',
        'name_ar',
        'name_en',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'city_id' => 'integer',
        ];
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(GeoCity::class, 'city_id');
    }
}
