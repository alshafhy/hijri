<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report\Cards;

use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Property;

/**
 * Legacy `locationDetails`: basic property data and property borders cards.
 */
final class LocationCards
{
    private const SHARE_LAND_TYPES = ['شقة', 'دور', 'وحدة سكنية'];

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>, columns: int, lines: list<list<array{label: string, value: string}>>}|null
     */
    public function basic(Property $property): ?array
    {
        $location = $property->location;

        return Card::paired(Card::make(Card::label('basic_property_data'), [
            [Card::label('neighborhood'), $this->geoName($location?->neighborhood)],
            [Card::label('city'), $this->geoName($location?->city)],
            [Card::label('sketch_no'), Card::text($location?->sketch_no)],
            [Card::label('piece_number'), Card::text($location?->piece_number)],
            [Card::label('part_no'), Card::text($location?->part_no)],
            [Card::label('street_name'), Card::text($location?->street)],
        ]));
    }

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>, columns: int, lines: list<list<array{label: string, value: string}>>}|null
     */
    public function borders(Property $property): ?array
    {
        $border = $property->borders->first();
        $rows = [];
        $wide = [Card::label('total_land_area')];

        foreach (['north', 'south', 'east', 'west'] as $side) {
            $length = Card::positive($border?->{"{$side}_length"}, Card::label('meter'));
            $rows[] = [Card::label($side), Card::text($border?->{$side})];
            $rows[] = [Card::label('length'), $length];
            if ($length === null) {
                $wide[] = Card::label($side);
            }
        }

        $rows[] = [Card::label('total_land_area'), Card::positive($this->landArea($property), Card::label('square_meter_text'))];

        return Card::paired(Card::make(Card::label('property_borders'), $rows), $wide);
    }

    private function landArea(Property $property): ?float
    {
        $key = in_array($property->property_type, self::SHARE_LAND_TYPES, true) ? 'share_land' : 'land_area';
        $area = $property->components->firstWhere('component_key', $key)?->area_value;

        return is_numeric($area) ? (float) $area : null;
    }

    private function geoName(GeoCity|GeoNeighborhood|null $geo): ?string
    {
        if ($geo === null) {
            return null;
        }

        $name = app()->getLocale() === 'en' && filled($geo->name_en) ? $geo->name_en : $geo->name_ar;

        return Card::text($name);
    }
}
