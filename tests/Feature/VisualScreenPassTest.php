<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Contractor;
use App\Models\Offer;
use App\Models\Partner;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    foreach ([
        'dashboard.view',
        'valuation_request.view',
        'valuation_request.create',
        'partner.view',
        'partner.edit',
        'contractor.view',
        'contractor.edit',
        'contract.view',
        'contract.create',
        'offer.view',
        'offer.edit',
        'geo_city.view',
        'geo_neighborhood.view',
        'geo_neighborhood.create',
        'financial.view',
        'company.view',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions(Permission::all());
    Role::findOrCreate('coordinator', 'web');
    Role::findOrCreate('evaluator', 'web');
    app()->setLocale('ar');
});

it('renders key screens with responsive tables and no raw valuation states', function () {
    $manager = User::factory()->create(['status' => UserStatus::Active]);
    $manager->assignRole('manager');

    ValuationRequest::query()->create([
        'number' => 'VIS-1',
        'reference' => 1,
        'state' => 'underEvaluative',
    ]);
    Partner::query()->create(['name' => 'شريك واجهة', 'state' => 1]);
    Contractor::query()->create(['name' => 'مقاول واجهة', 'state' => 2, 'fees' => 10]);
    $offer = Offer::query()->create(['number' => 'OFF-VIS', 'state' => 0, 'partner_name' => 'شريك']);

    $screens = [
        route('home'),
        route('dashboard.valuation-requests.index'),
        route('dashboard.valuation-requests.create'),
        route('dashboard.partners.index'),
        route('dashboard.contractors.index'),
        route('dashboard.contracts.index'),
        route('dashboard.contracts.create'),
        route('dashboard.offers.index'),
        route('dashboard.offers.show', $offer),
        route('dashboard.geo-cities.index'),
        route('dashboard.geo-neighborhoods.index'),
        route('dashboard.financial.index'),
    ];

    foreach ($screens as $url) {
        $html = $this->actingAs($manager)->get($url)->assertOk()->getContent();
        expect($html)->not->toContain('>underEvaluative<')
            ->and($html)->not->toContain('Whoops');
    }

    $offerHtml = $this->actingAs($manager)->get(route('dashboard.offers.show', $offer))->getContent();
    expect($offerHtml)->toContain(route('dashboard.offers.estates.store', $offer, false));

    $list = $this->actingAs($manager)->get(route('dashboard.valuation-requests.index'))->getContent();
    expect($list)->toContain('تحت التقييم')
        ->and($list)->toContain('table-responsive');
});
