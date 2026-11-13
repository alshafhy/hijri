<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Lab404\Impersonate\Models\Impersonate;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property UserStatus|null $status
 * @property bool $can_approve_final_request
 * @property Carbon|null $invitation_sent_at
 */
class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasRoles;
    use Impersonate;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'phone_number',
        'membership_number',
        'membership_category',
        'membership_branch_type',
        'signature_path',
        'avatar_path',
        'password',
        'branch_id',
        'status',
        'can_approve_final_request',
        'invitation_token',
        'invitation_sent_at',
        'pass_need_to_be_changed',
        'last_login_at',
        'last_login_ip_address',
        'email_verified_at',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'invitation_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => UserStatus::class,
            'pass_need_to_be_changed' => 'integer',
            'branch_id' => 'integer',
            'can_approve_final_request' => 'boolean',
            'invitation_sent_at' => 'datetime',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('User')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly(['name', 'username', 'email', 'status', 'branch_id', 'membership_number', 'can_approve_final_request']);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'subject_id', 'id')
            ->where(['subject_type' => self::class]);
    }

    public function supportRequests(): HasMany
    {
        return $this->hasMany(SupportRequest::class);
    }

    public function hasPendingInvitation(): bool
    {
        return $this->invitation_token !== null;
    }

    public function isStaffMember(): bool
    {
        return $this->hasAnyRole(['coordinator', 'evaluator']);
    }

    public function canImpersonate(): bool
    {
        return $this->hasAnyRole(['super-admin', 'admin']);
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->hasRole('super-admin');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term === null || trim($term) === '') {
            return $query;
        }

        $like = '%'.trim($term).'%';

        return $query->where(function (Builder $builder) use ($like): void {
            $builder
                ->where('name', 'like', $like)
                ->orWhere('username', 'like', $like)
                ->orWhere('email', 'like', $like);
        });
    }

    public function scopeForBranch(Builder $query, ?int $branchId): Builder
    {
        if ($branchId === null) {
            return $query;
        }

        return $query->where('branch_id', $branchId);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', (int) config('users.status.active'));
    }
}
