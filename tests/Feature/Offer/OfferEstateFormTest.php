<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    foreach (['dashboard.view', 'offer.view', 'offer.edit'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions(['dashboard.view', 'offer.view', 'offer.edit']);
    app()->setLocale('ar');

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
    $this->partner = Partner::query()->create(['name' => 'عميل بند', 'state' => Partner::STATE_ACTIVE]);
    $this->offer = Offer::query()->create([
        'number' => 'OFF-ESTATE-UI',
        'partner_id' => $this->partner->id,
        'partner_name' => $this->partner->name,
        'state' => Offer::STATE_WAITING,
    ]);
});

it('shows kind and type dropdowns instead of duplicate نوع fields', function () {
    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.offers.show', $this->offer))
        ->assertOk()
        ->assertSee('نوع العقار', false)
        ->assertSee('صنف العقار', false)
        ->assertSee('js/scripts/pages/offer-estate-form.js', false)
        ->assertSee('name="estate_kind"', false)
        ->assertSee('name="estate_type"', false)
        ->assertSee('data-estate-kinds', false)
        ->assertSee('سكني', false)
        ->getContent();

    expect(substr_count($html, '>النوع<'))->toBe(0)
        ->and($html)->toContain('text-danger')
        ->and($html)->toContain('col-md-4');
});

it('rejects an estate type that does not belong to the selected kind', function () {
    $this->actingAs($this->manager)
        ->from(route('dashboard.offers.show', $this->offer))
        ->post(route('dashboard.offers.estates.store', $this->offer), [
            'estate_kind' => 'سكني',
            'estate_type' => 'سوق تجاري',
        ])
        ->assertSessionHasErrors('estate_type');

    expect(OfferEstate::query()->where('offer_id', $this->offer->id)->exists())->toBeFalse();
});

it('stores a valid kind and type from the valuation lists', function () {
    $city = GeoCity::query()->create(['legacy_id' => 9101, 'name_ar' => 'جدة']);
    GeoNeighborhood::query()->create(['legacy_id' => 9101, 'city_id' => $city->id, 'name_ar' => 'الروضة']);

    $this->actingAs($this->manager)
        ->post(route('dashboard.offers.estates.store', $this->offer), [
            'estate_kind' => 'سكني',
            'estate_type' => 'فيلا',
            'instrument_no' => '123456',
            'area' => 300,
            'neighborhood' => 'الروضة',
            'fees' => 500,
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $estate = OfferEstate::query()->where('offer_id', $this->offer->id)->first();
    expect($estate)->not->toBeNull()
        ->and($estate->estate_kind)->toBe('سكني')
        ->and($estate->estate_type)->toBe('فيلا')
        ->and($estate->neighborhood)->toBe('الروضة')
        ->and($estate->fees)->toBe(500);
});

it('shows a neighborhood dropdown and labels fees as amount', function () {
    $city = GeoCity::query()->create(['legacy_id' => 9102, 'name_ar' => 'الرياض']);
    GeoNeighborhood::query()->create(['legacy_id' => 9102, 'city_id' => $city->id, 'name_ar' => 'الملقا']);

    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.offers.show', $this->offer))
        ->assertOk()
        ->assertSee('المبلغ', false)
        ->assertSee('الملقا', false)
        ->assertSee('name="neighborhood"', false)
        ->assertSee('data-neighborhoods-by-city', false)
        ->getContent();

    expect($html)->not->toContain('الأتعاب')
        ->and($html)->toContain('<select')
        ->and($html)->not->toContain('id="estate_neighborhood"');
});
