<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report\Cards;

use App\Models\Property;
use App\Models\PropertyAc;
use App\Models\PropertyLand;

/**
 * Legacy `descriptionFinishes`, `servicesInfoTable` and `otherInfoTable` cards (built properties only).
 */
final class BuildingCards
{
    private const AC_LABELS = [
        'مركزي' => 'central_ac',
        'سبيلت' => 'split_ac',
        'سبليت' => 'split_ac',
        'شباك' => 'window_ac',
        'غير مركب' => 'not_installed',
        'مركب' => 'composite_no',
        'توجد التوصيلات فقط' => 'connections',
    ];

    private const QUESTIONS = [
        'populated' => 'is_property_occupied',
        'ready' => 'is_property_ready',
        'identical' => 'does_property_comply',
        'contrary' => 'is_property_violation',
        'concrete_roof' => 'is_reinforced_concrete',
        'ground_extension' => 'does_property_have_extension',
        'need_cost' => 'does_property_need_maintenance',
    ];

    private const SERVICES = ['electricity', 'water', 'sanitation', 'telephone', 'internet'];

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public function finishes(Property $property): ?array
    {
        $building = $property->building;
        $age = trim((string) $building?->build_age);

        return Card::make(Card::label('description_finishes_and_construction'), [
            [Card::label('architectural_design'), Card::text($building?->architectural ?: 'ممتاز')],
            [Card::label('insulation_quality'), Card::text($building?->quality_insulation ?: 'لا يوجد')],
            [Card::label('structural_frame'), Card::text($building?->structure_construction ?: 'خرساني')],
            [Card::label('ceiling_type'), Card::text($building?->bishop_type ?: 'خرسانة مسلحة')],
            [Card::label('building_age'), $age === '' || $age === '0' ? Card::label('new_building') : $age],
            [Card::label('building_condition'), Card::text($building?->build_state ?: 'ممتاز')],
            [Card::label('default_building_age'), Card::number($building->default_build_age ?? 0)],
            [Card::label('remaining_building_age'), Card::number($building->remaining_build_age ?? 0)],
            [Card::label('air_conditioning'), $this->acTypes($property->ac)],
            [Card::label('composite_no'), Card::positive($property->ac?->ac_count)],
        ]);
    }

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public function services(Property $property): ?array
    {
        $service = $property->services->first();
        $land = $property->lands->first();
        $rows = array_map(
            static fn (string $key): array => [Card::label($key), Card::text($service?->{$key})],
            self::SERVICES,
        );

        return Card::make(Card::label('services_available'), [
            ...$rows,
            [Card::label('electricity_counter'), $this->meter($land, 'electricity')],
            [Card::label('water_counter'), $this->meter($land, 'water')],
        ]);
    }

    /**
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public function details(Property $property): ?array
    {
        $question = $property->question;
        $rows = [];

        foreach (self::QUESTIONS as $column => $labelKey) {
            $rows[] = [Card::label($labelKey), Card::text($question?->getAttribute($column))];
        }

        $bestUse = $question?->best_use;
        $rows[] = [Card::label('best_use_current'), $bestUse === null ? null : Card::text($bestUse === 1 ? 'نعم' : 'لا')];
        $rows[] = [Card::label('inspection_type'), Card::label(
            trim((string) $question?->inside_inspection) === 'نعم' ? 'inspection_inside_outside' : 'inspection_outside_only'
        )];

        return Card::make(Card::label('Details related to the property'), $rows);
    }

    private function acTypes(?PropertyAc $ac): string
    {
        $types = array_values(array_filter(array_map('trim', explode(',', (string) $ac?->types))));
        $labels = array_map(
            static fn (string $type): string => isset(self::AC_LABELS[$type]) ? Card::label(self::AC_LABELS[$type]) : $type,
            $types,
        );

        return $labels === [] ? Card::label('not_available') : implode(' , ', $labels);
    }

    private function meter(?PropertyLand $land, string $kind): ?string
    {
        $count = Card::text($land?->{"{$kind}_no"});
        $numbers = Card::text($land?->{"{$kind}_num"});

        if ($count === null && $numbers === null) {
            return null;
        }

        return Card::join([
            $count === null ? null : Card::label('count_of_counter').' '.$count,
            $numbers === null ? null : Card::label('counter_numbers').' '.$numbers,
        ], ' ');
    }
}
