<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Contractor extends Model
{
    use LogsActivity;
    use SoftDeletes;

    public const STATE_DRAFT = 0;

    public const STATE_ACTIVE = 2;

    public const STATE_INACTIVE = -1;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Contractor')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['name', 'email', 'phone_number', 'fees', 'date_from', 'date_to', 'state']);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
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

    public function feesFormatted(): string
    {
        return number_format((float) ($this->fees ?? 0), 2);
    }
}
