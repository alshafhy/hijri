<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Company extends Model
{
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'legacy_id',
        'name',
        'name_en',
        'phone_number',
        'address',
        'company_membership_number',
        'membership_date',
        'accreditation_end_date',
        'company_reg_number',
        'main_logo_path',
        'signature_path',
        'stamp_path',
        'report_logo_path',
        'default_coordinator_share',
        'default_evaluator_share',
        'default_manager_share',
        'request_sla_hours',
        'evaluation_sla_hours',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'status' => 'integer',
            'membership_date' => 'date',
            'accreditation_end_date' => 'date',
            'default_coordinator_share' => 'integer',
            'default_evaluator_share' => 'integer',
            'default_manager_share' => 'integer',
            'request_sla_hours' => 'integer',
            'evaluation_sla_hours' => 'integer',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('Company')
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logOnly([
                'name',
                'name_en',
                'phone_number',
                'address',
                'company_membership_number',
                'membership_date',
                'accreditation_end_date',
                'company_reg_number',
                'main_logo_path',
                'signature_path',
                'stamp_path',
                'report_logo_path',
                'default_coordinator_share',
                'default_evaluator_share',
                'default_manager_share',
                'request_sla_hours',
                'evaluation_sla_hours',
                'status',
            ]);
    }

    public function valuationRequests(): HasMany
    {
        return $this->hasMany(ValuationRequest::class);
    }
}
