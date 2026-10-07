<?php

declare(strict_types=1);

use App\Enums\Commercial\OfferEstatePaymentStatus;
use App\Enums\Commercial\OfferEstateStatus;
use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Contract;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Models\Property;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $permissions = ['dashboard.view', 'valuation_request.view', 'valuation_request.create', 'valuation_request.edit'];
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('coordinator', 'web')->syncPermissions($permissions);
    Role::findOrCreate('evaluator', 'web')->syncPermissions(['dashboard.view', 'valuation_request.view', 'valuation_request.edit']);
    app()->setLocale('ar');
    Storage::fake('local');

    $this->coordinator = User::factory()->create(['status' => UserStatus::Active]);
    $this->coordinator->assignRole('coordinator');
    $this->evaluator = User::factory()->create(['status' => UserStatus::Active]);
    $this->evaluator->assignRole('evaluator');

    $this->city = GeoCity::query()->create(['legacy_id' => 8001, 'name_ar' => 'جدة']);
    $this->neighborhood = GeoNeighborhood::query()->create(['legacy_id' => 8001, 'city_id' => $this->city->id, 'name_ar' => 'الروضة']);
    $this->partner = Partner::query()->create(['name' => 'بنك', 'state' => Partner::STATE_ACTIVE]);
    $this->offer = Offer::query()->create(['legacy_id' => 8001, 'partner_id' => $this->partner->id, 'number' => 'OF-1', 'state' => Offer::STATE_ACCEPTED]);
    $this->estate = OfferEstate::query()->create([
        'legacy_id' => 8001,
        'offer_id' => $this->offer->id,
        'estate_type' => 'فيلا',
        'payment_status' => OfferEstatePaymentStatus::Unpaid,
        'status' => OfferEstateStatus::Active,
    ]);
});

function wizardPayload(array $overrides = []): array
{
    $test = test();

    return array_merge([
        'client_type' => 2,
        'partner_id' => $test->partner->id,
        'offer_estate_id' => $test->estate->id,
        'customer_name' => 'بنك',
        'owner_name' => 'مالك',
        'instrument_no' => '4100',
        'instrument_gregorian_date' => '2024-01-15',
        'according_instrument' => '1',
        'license_no' => 'L-77',
        'retail_no' => '12345',
        'city_id' => $test->city->id,
        'neighborhood_id' => $test->neighborhood->id,
        'part_no' => '12',
        'property_kind' => 'سكني',
        'property_type' => 'فيلا',
        'evaluator_user_id' => $test->evaluator->id,
        'borders' => ['north' => 'شارع', 'north_length' => '20.5', 'south' => 'جار'],
        'components' => ['land_area' => ['area_value' => 600], 'not_a_grid_key' => ['area_value' => 5]],
        'x_axis' => '21.5433',
        'y_axis' => '39.1728',
        'new_attachments' => [['file' => UploadedFile::fake()->create('deed.pdf', 20, 'application/pdf'), 'type' => 'صورة للصك']],
    ], $overrides);
}

it('renders the create wizard', function () {
    $this->actingAs($this->coordinator)
        ->get(route('dashboard.valuation-requests.create'))
        ->assertOk()
        ->assertSee('data-wizard', false)
        ->assertSee('name="client_type"', false)
        ->assertSee('borders[north_length]', false)
        ->assertSee('create-map', false);
});

it('creates a request linked to a partner estate with borders, areas, attachments and company shares', function () {
    Company::query()->create(['name' => 'شركة', 'default_coordinator_share' => 15, 'default_evaluator_share' => 35, 'default_manager_share' => 50]);

    $this->actingAs($this->coordinator)
        ->post(route('dashboard.valuation-requests.store'), wizardPayload())
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $request = ValuationRequest::query()->latest('id')->firstOrFail();
    $property = Property::query()->where('valuation_request_id', $request->id)->firstOrFail();

    expect($request->state)->toBe('waiting')
        ->and($request->reference)->toBe($request->id + 10000)
        ->and($request->evaluator_user_id)->toBe($this->evaluator->id)
        ->and($property->according_instrument)->toBeTrue()
        ->and($property->license_no)->toBe('L-77')
        ->and($property->location->neighborhood_id)->toBe($this->neighborhood->id)
        ->and($property->location->x_axis)->toBe('21.5433')
        ->and($property->borders()->first()->north_length)->toBe(20.5)
        ->and($property->components()->pluck('component_key')->all())->toBe(['land_area'])
        ->and(Contract::query()->count())->toBe(0);

    $link = PartnerContract::query()->where('valuation_request_id', $request->id)->firstOrFail();
    expect($link->partner_id)->toBe($this->partner->id)
        ->and($link->offer_estate_id)->toBe($this->estate->id);

    $share = $request->feeShares()->first();
    expect($share->coordinator_share)->toBe(15)
        ->and($share->evaluator_share)->toBe(35)
        ->and($share->manager_share)->toBe(50);

    $attachment = ValuationAttachment::query()->where('valuation_request_id', $request->id)->firstOrFail();
    Storage::disk('local')->assertExists($attachment->path);
});

it('rejects inconsistent wizard data', function (array $overrides, string $errorKey) {
    if ($errorKey === 'offer_estate_id') {
        $other = Partner::query()->create(['name' => 'آخر', 'state' => Partner::STATE_ACTIVE]);
        $overrides['partner_id'] = $other->id;
    }
    if ($errorKey === 'neighborhood_id') {
        $otherCity = GeoCity::query()->create(['legacy_id' => 8002, 'name_ar' => 'مكة']);
        $overrides['city_id'] = $otherCity->id;
    }

    $this->actingAs($this->coordinator)
        ->post(route('dashboard.valuation-requests.store'), wizardPayload($overrides))
        ->assertSessionHasErrors($errorKey);

    expect(ValuationRequest::query()->count())->toBe(0);
    Storage::disk('local')->assertDirectoryEmpty('/');
})->with([
    'estate of another partner' => [[], 'offer_estate_id'],
    'neighborhood of another city' => [[], 'neighborhood_id'],
    'both deed dates' => [['instrument_date' => '1445/07/04'], 'instrument_date'],
    'missing map location' => [['x_axis' => '0'], 'x_axis'],
    'subtype of another kind' => [['property_type' => 'محل'], 'property_type'],
    'contractor client without contractor' => [['client_type' => 1], 'contractor_id'],
]);

it('lists partner offers and offer estates for the wizard', function () {
    $this->actingAs($this->coordinator)
        ->getJson(route('dashboard.valuation-lookups.partner-offers', $this->partner))
        ->assertOk()
        ->assertJsonPath('0.id', $this->offer->id);

    $this->actingAs($this->coordinator)
        ->getJson(route('dashboard.valuation-lookups.offer-estates', $this->offer))
        ->assertOk()
        ->assertJsonPath('0.id', $this->estate->id);
});

it('edits initial data but blocks evaluators and approved requests', function () {
    $this->actingAs($this->coordinator)->post(route('dashboard.valuation-requests.store'), wizardPayload());
    $request = ValuationRequest::query()->latest('id')->firstOrFail();

    $this->actingAs($this->coordinator)
        ->get(route('dashboard.valuation-requests.edit', $request))
        ->assertOk()
        ->assertSee('deed.pdf');

    $payload = wizardPayload(['owner_name' => 'مالك معدل', 'borders' => ['west' => 'حديقة'], 'new_attachments' => []]);
    $this->actingAs($this->coordinator)
        ->put(route('dashboard.valuation-requests.update', $request), $payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('dashboard.valuation-requests.show', $request));

    $property = $request->property()->first();
    expect($property->owner_name)->toBe('مالك معدل')
        ->and($property->borders()->count())->toBe(1)
        ->and($property->borders()->first()->west)->toBe('حديقة')
        ->and($property->borders()->first()->north)->toBeNull();

    $request->forceFill(['evaluator_user_id' => $this->evaluator->id])->save();
    $this->actingAs($this->evaluator)
        ->get(route('dashboard.valuation-requests.edit', $request))
        ->assertForbidden();

    $request->forceFill(['state' => 'approve', 'approve' => $this->coordinator->id])->save();
    $this->actingAs($this->coordinator)
        ->put(route('dashboard.valuation-requests.update', $request), $payload)
        ->assertForbidden();
});
