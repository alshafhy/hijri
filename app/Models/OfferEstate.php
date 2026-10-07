<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commercial\OfferEstatePaymentStatus;
use App\Enums\Commercial\OfferEstateStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class OfferEstate extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'offer_id',
        'estate_kind',
        'estate_type',
        'instrument_no',
        'area',
        'neighborhood',
        'fees',
        'payment_status',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'offer_id' => 'integer',
            'area' => 'integer',
            'fees' => 'integer',
            'payment_status' => OfferEstatePaymentStatus::class,
            'status' => OfferEstateStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('OfferEstate')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['payment_status', 'status', 'fees', 'area', 'estate_type']);
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === OfferEstatePaymentStatus::Paid;
    }

    public function isActive(): bool
    {
        return $this->status === OfferEstateStatus::Active;
    }
}
