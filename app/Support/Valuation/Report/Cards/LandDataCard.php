<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report\Cards;

use App\Models\Property;
use App\Models\PropertyFacade;
use App\Models\PropertyFlooring;
use App\Models\PropertyLand;
use App\Support\Valuation\Report\ReportLabels;

/**
 * Legacy `mainInfoTable`: basic land data card.
 */
final class LandDataCard
{
    private const OTHER_OWNERSHIP = 4;

    private const OTHER_SECTOR = 16;

    private const FLOORING_AREAS = [
        'yards' => 'the_yard',
        'reception' => 'reception',
        'entrance' => 'entrance',
        'rooms' => 'the_rooms',
    ];

    public function __construct(private readonly ReportLabels $labels = new ReportLabels) {}

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public function build(Property $property): ?array
    {
        $land = $property->lands->first();

        return Card::make(Card::label('basic_land_data'), [
            ...$this->siteRows($property, $land),
            ...$this->codedRows($land),
            ...$this->buildingRows($property, $land),
            ...$this->facadeRows($property->facades->first()),
            [Card::label('flooring_typeco'), $this->flooring($property->flooring)],
            ...$this->contentRows($land),
            [Card::label('property_components_description'), Card::text($property->notes)],
        ]);
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function siteRows(Property $property, ?PropertyLand $land): array
    {
        return [
            [Card::label('location_field'), Card::text($land?->site)],
            [Card::label('land_nature'), Card::text($land?->land_nature)],
            [Card::label('streets'), Card::text($property->location?->street)],
            [Card::label('nearest_street'), Card::text($land?->street)],
            [Card::label('facades'), Card::text($land?->facade)],
            [Card::label('neighbor_field'), Card::text($land?->neighbor)],
            [Card::label('north_coordinate'), Card::text($property->location?->x_axis)],
            [Card::label('east_coordinate'), Card::text($property->location?->y_axis)],
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function codedRows(?PropertyLand $land): array
    {
        $ownership = $land?->ownership_type === self::OTHER_OWNERSHIP
            ? Card::text($land->ownership_description)
            : $this->labels->option('ownership_type', $land?->ownership_type);
        $use = (int) $land?->property_use === self::OTHER_SECTOR
            ? Card::text($land?->sector_description)
            : $this->labels->option('origin_sector', $land?->property_use);

        return [
            [Card::label('ownership_type'), $ownership],
            [Card::label('ownership_usage'), $use],
            [Card::label('building_type'), $this->labels->option('building_type', $land?->building_type)],
            [Card::label('finishing_status'), $this->labels->option('finishing_status', $land?->finishing_status)],
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function buildingRows(Property $property, ?PropertyLand $land): array
    {
        $totalArea = $property->total?->total_area ?: $land?->total_building_area;

        return [
            [Card::label('street_width'), Card::positive($land?->street_width)],
            [Card::label('total_building_area'), Card::positive($totalArea)],
            [Card::label('additional_features'), Card::text($land?->additional_features)],
            [Card::label('approved_floors'), Card::positive($land?->approved_floors)],
            [Card::label('furnishing_status'), $this->labels->option('furnishing_status', $land?->furnishing_status)],
            [Card::label('approved_building'), Card::positive($land?->approved_building)],
            [Card::label('surrounding_facilities'), $this->csv($land?->surrounding_facilities)],
        ];
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function facadeRows(?PropertyFacade $facade): array
    {
        $title = Card::label('facade_typen');
        $side = static fn (string $key, mixed $value): ?string => ($text = Card::text($value)) === null
            ? null
            : Card::label($key).': '.$text;

        return [
            [
                $title.' ('.Card::label('north').' - '.Card::label('south').')',
                Card::join([$side('north', $facade?->facade_type_n), $side('south', $facade?->facade_type_s)], ' | '),
            ],
            [
                $title.' ('.Card::label('east').' - '.Card::label('west').')',
                Card::join([$side('east', $facade?->facade_type_e), $side('west', $facade?->facade_type_w)], ' | '),
            ],
        ];
    }

    /**
     * Legacy prints only the descriptions of floors whose type is "other".
     */
    private function flooring(?PropertyFlooring $flooring): ?string
    {
        if ($flooring === null) {
            return null;
        }

        $parts = [];
        foreach (self::FLOORING_AREAS as $area => $labelKey) {
            $type = (string) $flooring->getAttribute("flooring_{$area}");
            $description = trim((string) $flooring->getAttribute("{$area}_description"));
            $isOther = str_contains($type, 'اخري') || str_contains($type, 'أخرى') || stripos($type, 'other') !== false;
            if ($isOther && $description !== '') {
                $parts[] = Card::label($labelKey).': '.$description;
            }
        }

        return Card::join($parts, ' | ');
    }

    /**
     * @return list<array{0: string, 1: ?string}>
     */
    private function contentRows(?PropertyLand $land): array
    {
        $include = $this->csv($land?->include);
        if ($include === null) {
            return [];
        }

        $counts = Card::join([
            $this->count('heater_no', $land?->heater_no),
            $this->count('lift_no', $land?->lift_no),
            $this->count('bathroonA_no', $land?->bathroom_a_no),
            $this->count('bathroonE_no', $land?->bathroom_e_no),
        ], ' | ');

        return [
            [Card::label('description_contents'), trim(Card::label('there_is_in_property')).' '.$include],
            [Card::label('property_contents_number'), $counts],
        ];
    }

    private function count(string $labelKey, ?string $value): ?string
    {
        $text = Card::text($value);

        return $text === null ? null : Card::label($labelKey).': '.$text;
    }

    private function csv(?string $csv): ?string
    {
        $items = array_map(Card::text(...), explode(',', (string) $csv));

        return Card::join($items, ' '.Card::label('and').' ');
    }
}
