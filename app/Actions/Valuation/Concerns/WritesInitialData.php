<?php

declare(strict_types=1);

namespace App\Actions\Valuation\Concerns;

use App\Models\Property;

trait WritesInitialData
{
    private const INITIAL_PROPERTY_FIELDS = [
        'customer_name', 'owner_name', 'instrument_no', 'instrument_date', 'instrument_gregorian_date',
        'license_no', 'license_date', 'issued_by', 'commissioning_date', 'retail_no', 'undergo_reasons',
        'notes_real_estate',
    ];

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function initialPropertyAttributes(array $data): array
    {
        $attributes = array_intersect_key($data, array_flip(self::INITIAL_PROPERTY_FIELDS));
        $attributes['according_instrument'] = (bool) ($data['according_instrument'] ?? false);

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function writeLocation(Property $property, array $data): void
    {
        $property->location()->updateOrCreate(['property_id' => $property->id], [
            'city_id' => $data['city_id'] ?? null,
            'neighborhood_id' => $data['neighborhood_id'] ?? null,
            'street' => $data['street'] ?? null,
            'sketch_no' => $data['sketch_no'] ?? null,
            'part_no' => $data['part_no'] ?? null,
            'piece_number' => $data['piece_number'] ?? null,
            'x_axis' => isset($data['x_axis']) ? (string) $data['x_axis'] : null,
            'y_axis' => isset($data['y_axis']) ? (string) $data['y_axis'] : null,
        ]);
    }

    /**
     * @param  array<string, mixed>  $borders
     */
    private function writeBorders(Property $property, array $borders): void
    {
        $attributes = [];
        foreach (['north', 'south', 'east', 'west'] as $side) {
            $attributes[$side] = $borders[$side] ?? null;
            $attributes["{$side}_length"] = isset($borders["{$side}_length"]) && $borders["{$side}_length"] !== ''
                ? (float) $borders["{$side}_length"]
                : null;
        }

        $existing = $property->borders()->first();
        $existing !== null
            ? $existing->update($attributes)
            : $property->borders()->create($attributes);
    }
}
