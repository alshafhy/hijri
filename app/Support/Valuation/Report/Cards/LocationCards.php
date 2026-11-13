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
        $lines = [];

        foreach (['north', 'south', 'east', 'west'] as $side) {
            $line = $this->borderLine($side, Card::text($border?->{$side}), Card::positive($border?->{"{$side}_length"}, Card::label('meter')));
            if ($line !== []) {
                $lines[] = $line;
            }
        }

        $area = Card::positive($this->landArea($property), Card::label('square_meter_text'));
        if ($area !== null) {
            $lines[] = [['label' => Card::label('total_land_area'), 'value' => $area]];
        }

        return $lines === [] ? null : [
            'title' => Card::label('property_borders'),
            'rows' => array_merge(...$lines),
            'columns' => 4,
            'lines' => $lines,
        ];
    }

    /**
     * One printed line per side: the side name with its own length; a name-less side with a length prints a dash.
     *
     * @return list<array{label: string, value: string}>
     */
    private function borderLine(string $side, ?string $name, ?string $length): array
    {
        if ($name === null && $length === null) {
            return [];
        }

        $line = [['label' => Card::label($side), 'value' => $name ?? '—']];
        if ($length !== null) {
            $line[] = ['label' => Card::label('length'), 'value' => $length];
        }

        return $line;
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
