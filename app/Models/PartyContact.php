<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Commercial\PartyContactOwnerType;
use App\Enums\Commercial\PartyContactStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PartyContact extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'partner_id',
        'contractor_id',
        'owner_type',
        'name',
        'email',
        'phone_number',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'partner_id' => 'integer',
            'contractor_id' => 'integer',
            'owner_type' => PartyContactOwnerType::class,
            'status' => PartyContactStatus::class,
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('PartyContact')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['name', 'email', 'phone_number', 'status', 'owner_type']);
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(Contractor::class);
    }

    public function isActive(): bool
    {
        return $this->status === PartyContactStatus::Active
            || $this->status === PartyContactStatus::Pending;
    }
}
