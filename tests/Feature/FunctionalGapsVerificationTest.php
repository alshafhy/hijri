<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\PartyContact;
use App\Models\Property;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    $all = [
        'dashboard.view',
        'valuation_request.view',
        'valuation_request.create',
        'valuation_request.edit',
        'valuation_request.reject',
        'valuation_request.cancel',
        'valuation_request.duplicate',
        'valuation_request.mark_evaluated',
        'valuation_request.send',
        'valuation_request.approve_final',
        'partner.view',
        'partner.create',
        'partner.edit',
        'contractor.view',
        'contractor.create',
        'contractor.edit',
        'contract.view',
        'contract.create',
        'contract.edit',
        'contract.mark_paid',
        'offer.view',
        'offer.create',
        'offer.edit',
        'geo_city.view',
        'geo_city.create',
        'geo_neighborhood.view',
        'geo_neighborhood.create',
        'geo_neighborhood.delete',
        'company.view',
        'company.edit',
    ];

    foreach ($all as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('manager', 'web')->syncPermissions($all);
    Role::findOrCreate('coordinator', 'web')->syncPermissions([
        'dashboard.view',
        'valuation_request.view',
        'valuation_request.edit',
    ]);
    Role::findOrCreate('evaluator', 'web')->syncPermissions([
        'dashboard.view',
        'valuation_request.view',
        'valuation_request.edit',
        'valuation_request.mark_evaluated',
    ]);

    config(['activitylog.enabled' => true]);
});

function gapUser(string $role): User
{
    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->assignRole($role);

    return $user;
}

it('creates a contract via form stack and blocks evaluator', function () {
    $manager = gapUser('manager');
    $evaluator = gapUser('evaluator');

    $contractor = Contractor::query()->create([
        'name' => 'مقاول عقد',
        'state' => Contractor::STATE_ACTIVE,
        'fees' => 1000,
    ]);
    $valuation = ValuationRequest::query()->create([
        'number' => 'R-GAP-1',
        'state' => 'approve',
        'approve' => 1,
    ]);

    $this->actingAs($evaluator)
        ->post(route('dashboard.contracts.store'), [
            'contractor_id' => $contractor->id,
            'valuation_request_id' => $valuation->id,
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('dashboard.contracts.store'), [
            'contractor_id' => $contractor->id,
            'valuation_request_id' => $valuation->id,
            'state' => Contract::STATE_UNPAID,
        ])
        ->assertRedirect();

    $contract = Contract::query()->latest('id')->firstOrFail();
    expect($contract->contractor_id)->toBe($contractor->id)
        ->and($contract->valuation_request_id)->toBe($valuation->id)
        ->and(Activity::query()->where('log_name', 'Contract')->where('subject_id', $contract->id)->exists())->toBeTrue();

    $this->actingAs($manager)
        ->get(route('dashboard.contracts.index', ['sort' => 'id', 'dir' => 'desc']))
        ->assertOk()
        ->assertSee($contractor->name);
});

it('adds offer estate lines and party contacts with persistence and 403', function () {
    $manager = gapUser('manager');
    $evaluator = gapUser('evaluator');

    $partner = Partner::query()->create(['name' => 'شريك فجوة', 'state' => Partner::STATE_ACTIVE]);
    $offer = Offer::query()->create([
        'number' => 'OFF-GAP',
        'partner_id' => $partner->id,
        'partner_name' => $partner->name,
        'state' => Offer::STATE_WAITING,
    ]);
    $contractor = Contractor::query()->create(['name' => 'مقاول فجوة', 'state' => Contractor::STATE_ACTIVE]);

    $this->actingAs($evaluator)
        ->post(route('dashboard.offers.estates.store', $offer), [
            'estate_type' => 'فيلا',
            'fees' => 500,
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('dashboard.offers.estates.store', $offer), [
            'estate_type' => 'فيلا',
            'estate_kind' => 'سكني',
            'area' => 300,
            'fees' => 500,
        ])
        ->assertRedirect();

    expect(OfferEstate::query()->where('offer_id', $offer->id)->where('estate_type', 'فيلا')->exists())->toBeTrue();

    $this->actingAs($manager)
        ->get(route('dashboard.offers.show', $offer))
        ->assertOk()
        ->assertSee('فيلا');

    $this->actingAs($evaluator)
        ->post(route('dashboard.partners.contacts.store', $partner), [
            'name' => 'جهة',
            'email' => 'x@example.com',
        ])
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('dashboard.partners.contacts.store', $partner), [
            'name' => 'جهة شريك',
            'email' => 'partner-gap@example.com',
        ])
        ->assertRedirect();

    expect(PartyContact::query()->where('partner_id', $partner->id)->where('email', 'partner-gap@example.com')->exists())->toBeTrue();

    $this->actingAs($manager)
        ->post(route('dashboard.contractors.contacts.store', $contractor), [
            'name' => 'جهة مقاول',
            'email' => 'contractor-gap@example.com',
        ])
        ->assertRedirect();

    expect(PartyContact::query()->where('contractor_id', $contractor->id)->where('email', 'contractor-gap@example.com')->exists())->toBeTrue();
});

it('lists neighborhoods with city filter and sorting', function () {
    $manager = gapUser('manager');
    $cityA = GeoCity::query()->create(['legacy_id' => 1, 'name_ar' => 'مدينة أ']);
    $cityB = GeoCity::query()->create(['legacy_id' => 2, 'name_ar' => 'مدينة ب']);
    GeoNeighborhood::query()->create(['legacy_id' => 1, 'city_id' => $cityA->id, 'name_ar' => 'حي أ']);
    GeoNeighborhood::query()->create(['legacy_id' => 2, 'city_id' => $cityB->id, 'name_ar' => 'حي ب']);

    $this->actingAs($manager)
        ->get(route('dashboard.geo-neighborhoods.index', ['city_id' => $cityA->id, 'sort' => 'name_ar', 'dir' => 'asc']))
        ->assertOk()
        ->assertSee('حي أ')
        ->assertDontSee('حي ب');
});

it('rejects cancels and duplicates valuations with db persistence', function () {
    $manager = gapUser('manager');
    $evaluator = gapUser('evaluator');

    $request = ValuationRequest::query()->create([
        'number' => 'R-RCD',
        'state' => 'تم التقييم',
        'approve' => 0,
        'evaluator_user_id' => $evaluator->id,
        'coordinator_user_id' => $manager->id,
    ]);
    Property::query()->create([
        'valuation_request_id' => $request->id,
        'customer_name' => 'عميل',
        'property_kind' => 'سكني',
        'property_type' => 'شقة',
    ]);

    $this->actingAs($evaluator)
        ->post(route('dashboard.valuation-requests.reject', $request))
        ->assertForbidden();

    $this->actingAs($manager)
        ->post(route('dashboard.valuation-requests.reject', $request))
        ->assertRedirect();
    expect($request->fresh()->state)->toBe('تحت التقييم');

    $this->actingAs($manager)
        ->post(route('dashboard.valuation-requests.duplicate', $request))
        ->assertRedirect();
    $copy = ValuationRequest::query()->where('id', '!=', $request->id)->latest('id')->firstOrFail();
    expect($copy->property?->customer_name)->toBe('عميل')
        ->and($copy->state)->toBe('waiting');

    $this->actingAs($manager)
        ->post(route('dashboard.valuation-requests.cancel', $request))
        ->assertRedirect();
    expect(ValuationRequest::withTrashed()->find($request->id)?->trashed())->toBeTrue()
        ->and(ValuationRequest::withTrashed()->find($request->id)?->state)->toBe('cancelled');
});

it('uploads company logo signature and stamp for real', function () {
    $manager = gapUser('manager');
    Storage::fake('public');

    $company = Company::query()->create([
        'legacy_id' => 1,
        'name' => 'شركة اختبار',
    ]);

    foreach (['logo', 'signature', 'stamp'] as $type) {
        $file = UploadedFile::fake()->image("{$type}.png", 120, 80);
        $this->actingAs($manager)
            ->post(route('dashboard.companies.branding', $company), [
                'type' => $type,
                'image' => $file,
            ])
            ->assertRedirect();
    }

    $company->refresh();
    expect($company->main_logo_path)->not->toBeNull()
        ->and($company->signature_path)->not->toBeNull()
        ->and($company->stamp_path)->not->toBeNull();

    Storage::disk('public')->assertExists($company->main_logo_path);
    Storage::disk('public')->assertExists($company->signature_path);
    Storage::disk('public')->assertExists($company->stamp_path);
});

it('sorts valuation list server-side', function () {
    $manager = gapUser('manager');
    ValuationRequest::query()->create(['number' => 'A-1', 'reference' => 1, 'state' => 'waiting']);
    ValuationRequest::query()->create(['number' => 'Z-9', 'reference' => 9, 'state' => 'waiting']);

    $asc = $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.index', ['sort' => 'number', 'dir' => 'asc']))
        ->assertOk()
        ->getContent();

    expect(strpos($asc, 'A-1'))->toBeLessThan(strpos($asc, 'Z-9'));
});
