<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

use App\Models\Property;
use App\Support\Valuation\Report\Cards\BuildingCards;
use App\Support\Valuation\Report\Cards\LandDataCard;
use App\Support\Valuation\Report\Cards\LocationCards;

/**
 * Property description cards (location, borders, land data, finishes, services, details) for valuation reports.
 */
final class PropertyInfoSection
{
    public const RELATIONS = [
        'location.city', 'location.neighborhood', 'borders', 'lands', 'facades', 'flooring',
        'ac', 'building', 'question', 'services', 'components', 'total',
    ];

    private const BUILT_KINDS = ['سكني', 'تجاري'];

    public function __construct(
        private readonly LocationCards $location = new LocationCards,
        private readonly LandDataCard $land = new LandDataCard,
        private readonly BuildingCards $building = new BuildingCards,
    ) {}

    /**
     * @return list<array{title: string, rows: list<array{label: string, value: string}>}>
     */
    public function build(Property $property): array
    {
        $property->loadMissing(self::RELATIONS);

        $cards = [
            $this->location->basic($property),
            $this->location->borders($property),
            $this->land->build($property),
        ];

        if (in_array($property->property_kind, self::BUILT_KINDS, true)) {
            $cards[] = $this->building->finishes($property);
            $cards[] = $this->building->services($property);
            $cards[] = $this->building->details($property);
        }

        return array_values(array_filter($cards));
    }
}
