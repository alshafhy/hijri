<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Models\ValuationRequest;
use App\Support\Valuation\Report\Cards\Card;

/**
 * Legacy "basic evaluation data" card: request number, instrument date / number / issuer and the licence when set.
 */
final class BasicDataSection
{
    private const DASH = '—';

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public function build(ValuationRequest $request, ?Property $property): ?array
    {
        if ($property === null) {
            return null;
        }

        $rows = [
            [Card::label('request_number'), Card::text($request->number) ?? Card::text($request->reference) ?? self::DASH],
            [Card::label('date'), $this->instrumentDate($property)],
            [Card::label('instrument_number'), Card::text($property->instrument_no) ?? self::DASH],
            [Card::label('issued_by'), Card::text($property->issued_by) ?? self::DASH],
        ];

        $licence = Card::text($property->license_no);
        if ($licence !== null) {
            $rows[] = [Card::label('license_number'), $licence];
            $rows[] = [Card::label('license_date'), Card::text($property->license_date) ?? self::DASH];
        }

        return Card::make(Card::label('basic_evaluation_data'), $rows);
    }

    private function instrumentDate(Property $property): string
    {
        $date = Card::text($property->instrument_date) ?? Card::text($property->instrument_gregorian_date);
        if ($date !== null) {
            return $date;
        }

        return $property->according_instrument === true ? Card::label('according_to_instrument') : self::DASH;
    }
}
