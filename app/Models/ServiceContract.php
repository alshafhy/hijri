<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $contract_date
 * @property Carbon|null $instrument_date
 */
class ServiceContract extends Model
{
    protected $fillable = [
        'number',
        'company_id',
        'contract_date',
        'client_name',
        'client_id_number',
        'client_phone',
        'client_address',
        'client_email',
        'purpose',
        'city_id',
        'neighborhood_id',
        'property_kind',
        'property_type',
        'instrument_no',
        'instrument_date',
        'contract_value',
        'representative_name',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number' => 'integer',
            'contract_date' => 'date',
            'instrument_date' => 'date',
            'contract_value' => 'decimal:2',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(GeoCity::class, 'city_id');
    }

    public function neighborhood(): BelongsTo
    {
        return $this->belongsTo(GeoNeighborhood::class, 'neighborhood_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
