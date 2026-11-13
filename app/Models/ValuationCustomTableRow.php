<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValuationCustomTableRow extends Model
{
    protected $fillable = [
        'legacy_id',
        'valuation_request_id',
        'table_title',
        'label',
        'values',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'valuation_request_id' => 'integer',
            'values' => 'array',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ValuationRequest, $this>
     */
    public function valuationRequest(): BelongsTo
    {
        return $this->belongsTo(ValuationRequest::class);
    }
}
