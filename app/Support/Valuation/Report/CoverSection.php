<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Models\ValuationRequest;
use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy `reportCover` / `pageHeader-v2`: request and property values printed on the cover and page header.
 */
final class CoverSection
{
    public function __construct(private readonly ReportLabels $labels = new ReportLabels) {}

    /**
     * @return array{customer_name: ?string, property_kind: ?string, property_type: ?string, instrument_no: ?string, deposit_number: ?string, origin_sector: ?string}
     */
    public function build(ValuationRequest $request, ?Property $property): array
    {
        $deposit = Card::text($request->deposit_number) ?? Card::text($property?->deposit_number);

        return [
            'customer_name' => Card::text($property?->customer_name),
            'property_kind' => Card::text($property?->property_kind),
            'property_type' => Card::text($property?->property_type),
            'instrument_no' => Card::text($property?->instrument_no),
            'deposit_number' => $deposit,
            'origin_sector' => $this->labels->option('origin_sector', $property?->lands->first()?->property_use),
        ];
    }
}
