<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Valuation\RequestState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class ValuationRequest extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'reference',
        'number',
        'deposit_number',
        'company_id',
        'coordinator_user_id',
        'evaluator_user_id',
        'sub_user_id',
        'fellow_user_id',
        'legacy_coordinator_id',
        'legacy_evaluator_id',
        'legacy_sub_user_id',
        'legacy_fellow_id',
        'legacy_location_id',
        'state',
        'approve',
        'started_at',
        'under_evaluation_at',
        'evaluated_at',
        'ended_at',
        'uploaded_on_qima',
        'official_report_path',
        'qima_locked_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'reference' => 'integer',
            'company_id' => 'integer',
            'coordinator_user_id' => 'integer',
            'evaluator_user_id' => 'integer',
            'sub_user_id' => 'integer',
            'fellow_user_id' => 'integer',
            'legacy_coordinator_id' => 'integer',
            'legacy_evaluator_id' => 'integer',
            'legacy_sub_user_id' => 'integer',
            'legacy_fellow_id' => 'integer',
            'legacy_location_id' => 'integer',
            'approve' => 'integer',
            'started_at' => 'datetime',
            'under_evaluation_at' => 'datetime',
            'evaluated_at' => 'datetime',
            'ended_at' => 'datetime',
            'uploaded_on_qima' => 'boolean',
            'qima_locked_at' => 'datetime',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function property(): HasOne
    {
        return $this->hasOne(Property::class);
    }

    public function feeShares(): HasMany
    {
        return $this->hasMany(RequestFeeShare::class);
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function offers(): HasMany
    {
        return $this->hasMany(Offer::class);
    }

    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_user_id');
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_user_id');
    }

    public function isQimaLocked(): bool
    {
        return $this->qima_locked_at !== null || $this->uploaded_on_qima === true;
    }

    public function isFinallyApproved(): bool
    {
        $mapped = RequestState::tryFromLegacy($this->state);

        return ($mapped === RequestState::Approve)
            || $this->approve === 1;
    }

    public function stateLabel(): string
    {
        return RequestState::labelFor($this->state);
    }
}

