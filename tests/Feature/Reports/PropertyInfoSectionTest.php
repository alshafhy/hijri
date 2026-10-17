<?php

declare(strict_types=1);

use App\Models\Property;
use App\Support\Valuation\Report\PropertyInfoSection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(fn () => app()->setLocale('ar'));

/**
 * @param  list<array{title: string, rows: list<array{label: string, value: string}>}>  $cards
 * @return list<array{label: string, value: string}>
 */
function propertyInfoCard(array $cards, string $title): array
{
    foreach ($cards as $card) {
        if ($card['title'] === $title) {
            return $card['rows'];
        }
    }

    throw new RuntimeException("Card [{$title}] not found");
}

/**
 * @param  list<array{label: string, value: string}>  $rows
 */
function propertyInfoValue(array $rows, string $label): ?string
{
    foreach ($rows as $row) {
        if ($row['label'] === $label) {
            return $row['value'];
        }
    }

    return null;
}

it('builds the legacy property cards with coded values resolved to labels', function () {
    $cards = (new PropertyInfoSection)->build(reportFixture()->property);

    expect(array_column($cards, 'title'))->toBe([
        __('pdf.basic_property_data'),
        __('pdf.property_borders'),
        __('pdf.basic_land_data'),
        __('pdf.description_finishes_and_construction'),
        __('pdf.services_available'),
        __('pdf.Details related to the property'),
    ]);

    $basic = propertyInfoCard($cards, __('pdf.basic_property_data'));
    expect(propertyInfoValue($basic, __('pdf.neighborhood')))->toBe('الملقا')
        ->and(propertyInfoValue($basic, __('pdf.city')))->toBe('الرياض')
        ->and(propertyInfoValue($basic, __('pdf.sketch_no')))->toBe('1234')
        ->and(propertyInfoValue($basic, __('pdf.street_name')))->toBe('شارع الاختبار');

    $borders = propertyInfoCard($cards, __('pdf.property_borders'));
    expect($borders[0])->toBe(['label' => __('pdf.north'), 'value' => 'شارع عرض 20م'])
        ->and($borders[1])->toBe(['label' => __('pdf.length'), 'value' => '25 '.__('pdf.meter')])
        ->and(propertyInfoValue($borders, __('pdf.total_land_area')))->toBe('500 '.__('pdf.square_meter_text'));

    $land = propertyInfoCard($cards, __('pdf.basic_land_data'));
    expect(propertyInfoValue($land, __('pdf.ownership_type')))->toBe('ملكية')
        ->and(propertyInfoValue($land, __('pdf.ownership_usage')))->toBe('سكني')
        ->and(propertyInfoValue($land, __('pdf.building_type')))->toBe('جزء من مبني')
        ->and(propertyInfoValue($land, __('pdf.finishing_status')))->toBe('بدون تشطيب')
        ->and(propertyInfoValue($land, __('pdf.surrounding_facilities')))->toBe('مسجد و مدرسة')
        ->and(propertyInfoValue($land, __('pdf.north_coordinate')))->toBe('24.7136')
        ->and(propertyInfoValue($land, __('pdf.street_width')))->toBe('20')
        ->and(propertyInfoValue($land, __('pdf.property_components_description')))->toBe('وصف المكونات');

    $finishes = propertyInfoCard($cards, __('pdf.description_finishes_and_construction'));
    expect(propertyInfoValue($finishes, __('pdf.building_age')))->toBe('10')
        ->and(propertyInfoValue($finishes, __('pdf.ceiling_type')))->toBe('خرسانة مسلحة')
        ->and(propertyInfoValue($finishes, __('pdf.air_conditioning')))->toBe(__('pdf.split_ac'))
        ->and(propertyInfoValue($finishes, __('pdf.composite_no')))->toBeNull();
});

it('shows the composite ac count inline within the air conditioning row', function () {
    $property = reportFixture()->property;
    $property->ac()->update(['types' => 'سبليت,مركب', 'ac_count' => 3]);

    $finishes = propertyInfoCard((new PropertyInfoSection)->build($property->refresh()), __('pdf.description_finishes_and_construction'));

    expect(propertyInfoValue($finishes, __('pdf.air_conditioning')))->toBe(__('pdf.split_ac').' , '.__('pdf.composite_no').' (3)')
        ->and(propertyInfoValue($finishes, __('pdf.composite_no')))->toBeNull();
});

it('skips "other" flooring areas whose description is blank', function () {
    $property = reportFixture()->property;
    $property->flooring()->update([
        'flooring_yards' => 'أخرى', 'yards_description' => '  ',
        'flooring_rooms' => 'أخرى', 'rooms_description' => 'باركيه',
    ]);

    $land = propertyInfoCard((new PropertyInfoSection)->build($property->refresh()), __('pdf.basic_land_data'));

    expect(propertyInfoValue($land, __('pdf.flooring_typeco')))->toBe(__('pdf.the_rooms').': باركيه');
});

it('resolves the "other" ownership and usage codes to their descriptions', function () {
    $property = reportFixture()->property;
    $property->lands()->update([
        'ownership_type' => 4, 'ownership_description' => 'وقف',
        'property_use' => '16', 'sector_description' => 'استخدام خاص',
    ]);

    $land = propertyInfoCard((new PropertyInfoSection)->build($property->refresh()), __('pdf.basic_land_data'));

    expect(propertyInfoValue($land, __('pdf.ownership_type')))->toBe('وقف')
        ->and(propertyInfoValue($land, __('pdf.ownership_usage')))->toBe('استخدام خاص');
});

it('drops rows whose value is empty', function () {
    $property = reportFixture()->property;
    $property->lands()->update(['furnishing_status' => null, 'site' => '  ']);

    $cards = (new PropertyInfoSection)->build($property->refresh());
    $land = propertyInfoCard($cards, __('pdf.basic_land_data'));

    expect(propertyInfoValue($land, __('pdf.furnishing_status')))->toBeNull()
        ->and(propertyInfoValue($land, __('pdf.location_field')))->toBeNull()
        ->and(propertyInfoValue($land, __('pdf.additional_features')))->toBeNull()
        ->and(propertyInfoValue($land, __('pdf.flooring_typeco')))->toBeNull();

    foreach ($cards as $card) {
        expect($card['rows'])->not->toBeEmpty();
        foreach ($card['rows'] as $row) {
            expect(trim($row['value']))->not->toBe('');
        }
    }
});

it('omits the finishes, services and details cards for land', function () {
    $property = reportFixture()->property;
    $property->update(['property_kind' => 'أرض', 'property_type' => 'أرض']);

    $titles = array_column((new PropertyInfoSection)->build($property->refresh()), 'title');

    expect($titles)->toBe([
        __('pdf.basic_property_data'),
        __('pdf.property_borders'),
        __('pdf.basic_land_data'),
    ]);
});

it('shows best use and the inspection type in the property details card', function () {
    $property = reportFixture()->property;
    $details = propertyInfoCard((new PropertyInfoSection)->build($property), __('pdf.Details related to the property'));

    expect(propertyInfoValue($details, __('pdf.best_use_current')))->toBe('نعم')
        ->and(propertyInfoValue($details, __('pdf.inspection_type')))->toBe('معاينة العقار من الداخل والخارج')
        ->and(propertyInfoValue($details, __('pdf.is_property_violation')))->toBe('لا');

    $property->question()->update(['inside_inspection' => 'لا', 'best_use' => 0]);
    $details = propertyInfoCard((new PropertyInfoSection)->build($property->refresh()), __('pdf.Details related to the property'));

    expect(propertyInfoValue($details, __('pdf.best_use_current')))->toBe('لا')
        ->and(propertyInfoValue($details, __('pdf.inspection_type')))->toBe('معاينة العقار من الخارج');
});

it('loads its relations once instead of querying per card', function () {
    $property = Property::query()->findOrFail(reportFixture()->property->id);
    $section = new PropertyInfoSection;
    $section->build($property);

    DB::enableQueryLog();
    $section->build($property);

    expect(DB::getQueryLog())->toBe([]);
});
