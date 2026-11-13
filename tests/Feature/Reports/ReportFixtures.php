<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Property;
use App\Models\PropertyAc;
use App\Models\PropertyAdjustment;
use App\Models\PropertyBorder;
use App\Models\PropertyBuilding;
use App\Models\PropertyComparable;
use App\Models\PropertyComponent;
use App\Models\PropertyFacade;
use App\Models\PropertyFlooring;
use App\Models\PropertyLand;
use App\Models\PropertyLocation;
use App\Models\PropertyQuestion;
use App\Models\PropertyService;
use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Fully populated valuation request (سكني / فيلا) shared by the report builder tests.
 * Fakes the 'local' disk; users' signatures and the image attachment live on it.
 */
function reportFixture(): ValuationRequest
{
    Storage::fake('local');

    $request = ValuationRequest::query()->create([
        'number' => 'R-REPORT-1',
        'reference' => 77001,
        'deposit_number' => 'DEP-1',
        'company_id' => reportFixtureCompany()->id,
        'evaluator_user_id' => reportFixtureUser('مقيم الاختبار', ['membership_number' => '1210000001', 'membership_category' => 'أساسي', 'membership_branch_type' => 'عقار'])->id,
        'coordinator_user_id' => reportFixtureUser('منسق الاختبار', ['membership_number' => '1210000002'])->id,
        'sub_user_id' => reportFixtureUser('معتمد الاختبار', ['membership_number' => '1210000003', 'membership_category' => 'زميل'])->id,
        'state' => 'approve',
        'approve' => 1,
    ]);

    $property = reportFixtureProperty($request);
    reportFixtureLocation($property);
    reportFixtureDescriptions($property);
    reportFixtureComparables($property);
    reportFixtureCosts($property);
    reportFixtureCustomTable($request);
    reportFixtureAttachments($request);

    return $request->refresh();
}

function reportFixtureCompany(): Company
{
    return Company::query()->create([
        'name' => 'شركة التقييم للاختبار',
        'address' => 'الرياض',
        'company_membership_number' => '1110000001',
        'company_reg_number' => '1010000001',
        'membership_date' => '2017-01-01',
        'accreditation_end_date' => '2027-09-15',
    ]);
}

/**
 * @param  array<string, string>  $membership
 */
function reportFixtureUser(string $name, array $membership): User
{
    $user = User::factory()->create(['name' => $name] + $membership);
    $path = UploadedFile::fake()->image('signature.png', 120, 60)->store("users/{$user->id}", 'local');
    $user->update(['signature_path' => $path]);

    return $user;
}

function reportFixtureProperty(ValuationRequest $request): Property
{
    return Property::query()->create([
        'valuation_request_id' => $request->id,
        'customer_name' => 'عميل الاختبار',
        'owner_name' => 'مالك الاختبار',
        'property_kind' => 'سكني',
        'property_type' => 'فيلا',
        'instrument_no' => '310101010101',
        'instrument_date' => '1445/01/01',
        'valuation_type' => 1,
        'base_value' => 1,
        'value_assumption' => 4,
        'valuation_usage' => 1,
        'market_valuation_way_main' => 1,
        'cost_valuation_way_main' => 1,
        'informations_source' => 'منصة السوق',
        'evaluation_date' => '2026-10-01',
        'evaluation_date_hijri' => '1448/04/09',
        'deposit_number' => 'DEP-1',
        'notes' => 'وصف المكونات',
    ]);
}

function reportFixtureLocation(Property $property): void
{
    $city = GeoCity::query()->create(['legacy_id' => 97001, 'name_ar' => 'الرياض']);
    $neighborhood = GeoNeighborhood::query()->create(['legacy_id' => 97001, 'city_id' => $city->id, 'name_ar' => 'الملقا']);

    PropertyLocation::query()->create([
        'property_id' => $property->id,
        'city_id' => $city->id,
        'neighborhood_id' => $neighborhood->id,
        'sketch_name' => 'مخطط الاختبار',
        'street' => 'شارع الاختبار',
        'sketch_no' => '1234',
        'part_no' => '12',
        'piece_number' => '56',
        'x_axis' => '24.7136',
        'y_axis' => '46.6753',
    ]);

    PropertyBorder::query()->create([
        'property_id' => $property->id,
        'north' => 'شارع عرض 20م', 'north_length' => '25',
        'south' => 'جار', 'south_length' => '25',
        'east' => 'جار', 'east_length' => '20',
        'west' => 'ممر', 'west_length' => '20',
    ]);
}

function reportFixtureDescriptions(Property $property): void
{
    $id = ['property_id' => $property->id];

    PropertyLand::query()->create($id + [
        'land_nature' => 'مستوية', 'facade' => 'شمالية', 'site' => 'داخلي', 'street' => 'سكني',
        'neighbor' => 'سكني', 'include' => 'غرف', 'heater_no' => '2', 'lift_no' => '1',
        'bathroom_a_no' => '3', 'bathroom_e_no' => '2', 'ownership_type' => 1, 'building_type' => 1,
        'finishing_status' => 1, 'street_width' => 20, 'total_building_area' => 450, 'approved_floors' => 2,
        'property_use' => 1, 'surrounding_facilities' => 'مسجد,مدرسة',
    ]);
    PropertyBuilding::query()->create($id + [
        'build_age' => '10', 'build_state' => 'جيد', 'architectural' => 'حديث', 'quality_insulation' => 'جيد',
        'structure_construction' => 'خرساني', 'default_build_age' => 50, 'remaining_build_age' => 40,
    ]);
    PropertyFacade::query()->create($id + ['facade_type_n' => 'حجر', 'facade_type_s' => 'دهان', 'facade_type_e' => 'دهان', 'facade_type_w' => 'دهان']);
    PropertyFlooring::query()->create($id + ['flooring_yards' => 'بلاط', 'flooring_reception' => 'رخام', 'flooring_entrance' => 'رخام', 'flooring_rooms' => 'سيراميك']);
    PropertyAc::query()->create($id + ['types' => 'سبليت', 'ac_count' => 6]);
    PropertyQuestion::query()->create($id + [
        'populated' => 'نعم', 'ready' => 'نعم', 'identical' => 'نعم', 'contrary' => 'لا', 'concrete_roof' => 'نعم',
        'ground_extension' => 'لا', 'need_cost' => 'لا', 'inside_inspection' => 'نعم', 'best_use' => 1,
    ]);
    PropertyService::query()->create($id + ['electricity' => 'نعم', 'water' => 'نعم', 'sanitation' => 'نعم', 'telephone' => 'نعم', 'internet' => 'نعم']);
}

function reportFixtureComparables(Property $property): void
{
    $rows = [
        1 => ['area' => 500, 'price' => 3000, 'market' => 5, 'area_pct' => -2, 'weight' => 40],
        2 => ['area' => 600, 'price' => 2800, 'market' => 3, 'area_pct' => 0, 'weight' => 30],
        3 => ['area' => 550, 'price' => 3100, 'market' => 0, 'area_pct' => 2, 'weight' => 30],
    ];

    foreach ($rows as $sequence => $row) {
        PropertyComparable::query()->create([
            'property_id' => $property->id,
            'sequence' => $sequence,
            'real_estate_type' => 'أرض',
            'street_width' => 20,
            'front_number' => 1,
            'front_type' => 'شمالية',
            'area' => $row['area'],
            'price' => $row['price'],
            'percentage' => 100,
            'coordinates_url' => 'https://www.google.com/maps/@24.71'.$sequence.',46.67'.$sequence.',17z',
            'information_source' => 'منصة السوق',
        ]);
        PropertyAdjustment::query()->create([
            'property_id' => $property->id,
            'type' => $sequence,
            'transaction_date' => '2026-09-0'.$sequence,
            'market_conditions_percentage' => $row['market'],
            'area_percentage' => $row['area_pct'],
            'location_status_value' => 1,
            'location_status_percentage' => 0,
            'proportional_adjustment' => $row['weight'],
            'meter_price' => 3050,
            'meter_price_approximately' => 3000,
        ]);
    }
}

function reportFixtureCosts(Property $property): void
{
    $components = ['land_area' => [500, 3000], 'ground' => [300, 1500], 'upper' => [150, 1200]];
    foreach ($components as $key => [$area, $price]) {
        PropertyComponent::query()->create([
            'property_id' => $property->id,
            'component_key' => $key,
            'area_value' => $area,
            'price_value' => $price,
        ]);
    }

    PropertyTotal::query()->create([
        'property_id' => $property->id,
        'profit_percentage' => 10,
        'profit_amount' => 63_000,
        'depreciation_type' => 1,
        'depreciation_amount' => 138_600,
        'forced_sale_percentage' => 15,
        'forced_sale_amount' => 300_000,
        'total_amount' => 2_054_400,
        'total_amount_manual' => 2_000_000,
        'movables' => 0,
    ]);
}

function reportFixtureCustomTable(ValuationRequest $request): void
{
    $rows = [['label' => 'الإيجار السنوي', 'values' => ['120000', '5%']], ['label' => 'المصاريف', 'values' => ['10000']]];
    foreach ($rows as $order => $row) {
        ValuationCustomTableRow::query()->create($row + [
            'valuation_request_id' => $request->id,
            'table_title' => 'جدول الدخل',
            'sort_order' => $order,
        ]);
    }
}

function reportFixtureAttachments(ValuationRequest $request): void
{
    $files = [
        ['file' => UploadedFile::fake()->image('front.jpg', 200, 150), 'mime' => 'image/jpeg', 'type' => 'صورة للعقار'],
        ['file' => UploadedFile::fake()->create('deed.pdf', 20, 'application/pdf'), 'mime' => 'application/pdf', 'type' => 'صورة للصك'],
    ];

    foreach ($files as $item) {
        ValuationAttachment::query()->create([
            'valuation_request_id' => $request->id,
            'type' => $item['type'],
            'original_name' => $item['file']->getClientOriginalName(),
            'path' => $item['file']->store("valuation-attachments/{$request->id}", 'local'),
            'disk' => 'local',
            'mime_type' => $item['mime'],
            'size' => $item['file']->getSize(),
        ]);
    }
}
