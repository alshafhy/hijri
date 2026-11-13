<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeoCity extends Model
{
    protected $table = 'geo_cities';

    protected $fillable = [
        'legacy_id',
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
        ];
    }

    public function neighborhoods(): HasMany
    {
        return $this->hasMany(GeoNeighborhood::class, 'city_id');
    }
}
