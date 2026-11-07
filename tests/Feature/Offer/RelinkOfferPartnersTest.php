<?php

declare(strict_types=1);

use App\Actions\Offer\RelinkOfferPartnersAction;
use App\Enums\UserStatus;
use App\Models\Offer;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Permission::findOrCreate('valuation_request.view', 'web');
    Role::findOrCreate('coordinator', 'web')->syncPermissions(['valuation_request.view']);
});

it('links imported offers whose partner field is a legacy partner id', function () {
    $partner = Partner::query()->create([
        'legacy_id' => 9,
        'name' => 'حمد سعود الخرجي ',
        'state' => Partner::STATE_ACTIVE,
    ]);

    $offer = Offer::query()->create([
        'number' => '1',
        'partner_id' => null,
        'partner_name' => '9',
        'state' => Offer::STATE_WAITING,
    ]);

    $summary = app(RelinkOfferPartnersAction::class)();

    expect($summary['linked'])->toBe(1)
        ->and($offer->refresh()->partner_id)->toBe($partner->id)
        ->and($offer->partner_name)->toBe($partner->name);

    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->assignRole('coordinator');

    $this->actingAs($user)
        ->getJson(route('dashboard.valuation-lookups.partner-offers', $partner))
        ->assertOk()
        ->assertJsonPath('0.id', $offer->id);
});

it('links offers by trimmed partner name when the field is not numeric', function () {
    $partner = Partner::query()->create([
        'name' => 'بنك الاختبار',
        'state' => Partner::STATE_ACTIVE,
    ]);

    $offer = Offer::query()->create([
        'number' => 'OFF-NAME',
        'partner_id' => null,
        'partner_name' => 'بنك الاختبار',
        'state' => Offer::STATE_ACCEPTED,
    ]);

    app(RelinkOfferPartnersAction::class)();

    expect($offer->refresh()->partner_id)->toBe($partner->id);
});
