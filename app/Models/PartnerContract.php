<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Links a valuation request to a partner (client) and optionally one offer estate (legacy `contract2`).
 *
 * @property-read Partner|null $partner
 * @property-read OfferEstate|null $offerEstate
 * @property-read ValuationRequest|null $valuationRequest
 */
class PartnerContract extends Model
{
    protected $fillable = [
        'legacy_id',
        'partner_id',
        'offer_estate_id',
        'valuation_request_id',
        'legacy_partner_id',
        'legacy_estate_id',
        'legacy_request_id',
        'state',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'legacy_id' => 'integer',
            'partner_id' => 'integer',
            'offer_estate_id' => 'integer',
            'valuation_request_id' => 'integer',
            'legacy_partner_id' => 'integer',
            'legacy_estate_id' => 'integer',
            'legacy_request_id' => 'integer',
            'state' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Partner, $this>
     */
    public function partner(): BelongsTo
    {
        return $this->belongsTo(Partner::class);
    }

    /**
     * @return BelongsTo<OfferEstate, $this>
     */
    public function offerEstate(): BelongsTo
    {
        return $this->belongsTo(OfferEstate::class);
    }

    /**
     * @return BelongsTo<ValuationRequest, $this>
     */
    public function valuationRequest(): BelongsTo
    {
        return $this->belongsTo(ValuationRequest::class);
    }
}
