<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Offer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'legacy_user_id',
        'number',
        'partner_name',
        'partner_id',
        'city',
        'offered_at',
        'state',
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
            'partner_id' => 'integer',
            'offered_at' => 'datetime',
            'state' => 'integer',
            'created_by_legacy_user_id' => 'integer',
        ];
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }
}
