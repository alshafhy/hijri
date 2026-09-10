<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'legacy_user_id',
        'name',
        'email',
        'phone_number',
        'x_axis',
        'y_axis',
        'date_from',
        'date_to',
        'fees',
        'state',
        'template_id',
        'created_by_legacy_user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'legacy_user_id' => 'integer',
            'x_axis' => 'float',
            'y_axis' => 'float',
            'date_from' => 'date',
            'date_to' => 'date',
            'fees' => 'integer',
            'state' => 'integer',
            'template_id' => 'integer',
            'created_by_legacy_user_id' => 'integer',
        ];
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }
}
