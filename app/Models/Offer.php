<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Offer extends Model
{
    use LogsActivity;
    use SoftDeletes;

    public const STATE_WAITING = 0;

    public const STATE_REJECTED = 1;

    public const STATE_ACCEPTED = 2;

    protected $fillable = [
        'legacy_id',
        'valuation_request_id',
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
            'valuation_request_id' => 'integer',
            'legacy_user_id' => 'integer',
            'partner_id' => 'integer',
            'offered_at' => 'datetime',
            'state' => 'integer',
            'created_by_legacy_user_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Offer')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['number', 'partner_id', 'partner_name', 'city', 'state', 'offered_at']);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function valuationRequest(): BelongsTo
    {
        return $this->belongsTo(ValuationRequest::class);
    }

    public function estates(): HasMany
    {
        return $this->hasMany(OfferEstate::class);
    }

    public function isAccepted(): bool
    {
        return $this->state === self::STATE_ACCEPTED;
    }
}
