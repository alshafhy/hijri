<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Partner extends Model
{
    use LogsActivity;
    use SoftDeletes;

    public const STATE_DRAFT = 0;

    public const STATE_ACTIVE = 1;

    public const STATE_INACTIVE = -1;

    protected $fillable = [
        'legacy_id',
        'legacy_user_id',
        'name',
        'email',
        'phone_number',
        'x_axis',
        'y_axis',
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
            'state' => 'integer',
            'created_by_legacy_user_id' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Partner')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['name', 'email', 'phone_number', 'state']);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(PartyContact::class);
    }

    public function isActive(): bool
    {
        return $this->state === self::STATE_ACTIVE;
    }

    public function stateLabel(): string
    {
        return match ((int) $this->state) {
            self::STATE_ACTIVE => __('Active'),
            self::STATE_INACTIVE => __('Inactive'),
            self::STATE_DRAFT => __('Draft'),
            default => __('Unknown'),
        };
    }
}
