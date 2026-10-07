<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\ServiceContract;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\HijriDate;
use App\Support\Valuation\ValuationFieldGrid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $permissions = ['dashboard.view', 'company.view', 'company.edit', 'user.view', 'contractor.view', 'contract.mark_paid', 'valuation_request.view'];
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($permissions);
    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
});

it('converts Gregorian dates to Hijri', function (string $gregorian, string $hijri, string $day) {
    $date = HijriDate::fromGregorian($gregorian);

    expect($date->numeric())->toBe($hijri)
        ->and($date->dayName())->toBe($day);
})->with([
    ['2024-03-11', '1445/09/01', 'الإثنين'],
    ['2023-07-19', '1445/01/01', 'الأربعاء'],
    ['2017-01-01', '1438/04/02', 'الأحد'],
]);

it('creates numbered service contracts and renders the Hijri PDF', function () {
    Company::query()->create(['name' => 'شركة التقييم', 'company_reg_number' => '1010', 'company_membership_number' => '1210']);
    $city = GeoCity::query()->create(['legacy_id' => 1, 'name_ar' => 'الرياض', 'status' => 3]);
    $neighborhood = GeoNeighborhood::query()->create(['legacy_id' => 1, 'city_id' => $city->id, 'name_ar' => 'العقيق']);
    $kind = array_key_first(ValuationFieldGrid::kinds());
    $type = ValuationFieldGrid::kinds()[$kind][0];

    $payload = [
        'contract_date' => '2024-03-11', 'client_name' => 'عميل', 'client_id_number' => '1000000000', 'client_phone' => '0500000000',
        'purpose' => 'التمويل', 'city_id' => $city->id, 'neighborhood_id' => $neighborhood->id,
        'property_kind' => $kind, 'property_type' => $type, 'instrument_no' => '4455', 'instrument_date' => '2023-07-19',
        'contract_value' => 3500, 'representative_name' => 'المدير',
    ];

    $this->actingAs($this->manager)->get(route('dashboard.service-contracts.create'))->assertOk();
    $this->actingAs($this->manager)->post(route('dashboard.service-contracts.store'), $payload)->assertRedirect(route('dashboard.service-contracts.index'));
    $this->actingAs($this->manager)->post(route('dashboard.service-contracts.store'), $payload)->assertRedirect();

    expect(ServiceContract::query()->orderBy('number')->pluck('number')->all())->toBe([100, 101]);

    $contract = ServiceContract::query()->where('number', 100)->firstOrFail();
    $response = $this->actingAs($this->manager)->get(route('dashboard.service-contracts.pdf', $contract));
    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('pdf');

    $html = view('service_contracts.pdf', [
        'contract' => $contract->load('company', 'city', 'neighborhood'),
        'company' => $contract->company,
        'date' => HijriDate::fromGregorian($contract->contract_date),
        'instrumentDate' => HijriDate::fromGregorian($contract->instrument_date),
        'valueInWords' => 'ثلاثة آلاف وخمسمائة',
    ])->render();
    expect($html)->toContain('1445/09/01')->and($html)->toContain('1445/01/01')->and($html)->toContain('شركة التقييم');

    $other = ['neighborhood_id' => GeoNeighborhood::query()->create(['legacy_id' => 2, 'city_id' => GeoCity::query()->create(['legacy_id' => 2, 'name_ar' => 'جدة', 'status' => 3])->id, 'name_ar' => 'حي'])->id];
    $this->actingAs($this->manager)->post(route('dashboard.service-contracts.store'), array_merge($payload, $other))->assertSessionHasErrors('neighborhood_id');
});

it('stores landing contact messages and lists them for managers', function () {
    $this->get(route('home-page'))->assertOk()->assertSee('id="contact"', false);

    $this->post(route('landing.contact'), ['name' => 'زائر', 'email' => 'v@example.com', 'company' => 'شركة', 'phone_number' => '055', 'message' => 'أريد عرضاً'])
        ->assertRedirect(route('home-page').'#contact');
    $this->post(route('landing.contact'), ['name' => 'bot', 'email' => 'b@example.com', 'company' => 'x', 'phone_number' => '1', 'message' => 'spam', 'website' => 'http://spam'])
        ->assertSessionHasErrors('website');

    expect(ContactMessage::query()->count())->toBe(1);

    $message = ContactMessage::query()->sole();
    $this->actingAs($this->manager)->get(route('dashboard.contact-messages.index'))->assertOk()->assertSee('أريد عرضاً');
    $this->actingAs($this->manager)->put(route('dashboard.contact-messages.read', $message))->assertRedirect();
    expect($message->fresh()->read_at)->not->toBeNull();
});

it('shows the user manual and the contractor invoice', function () {
    $this->actingAs($this->manager)->get(route('dashboard.manual'))->assertOk()->assertSee('manual-how', false);

    $contractor = Contractor::query()->create(['name' => 'مقاول', 'fees' => 1500, 'state' => 2]);
    foreach ([0, 1] as $i => $state) {
        $request = ValuationRequest::query()->create(['number' => 'I-'.$i, 'reference' => 700 + $i, 'state' => 'approve', 'approve' => 1]);
        Contract::query()->create(['contractor_id' => $contractor->id, 'valuation_request_id' => $request->id, 'state' => $state]);
    }

    $this->actingAs($this->manager)->get(route('dashboard.contractors.show', $contractor))
        ->assertOk()
        ->assertSee(route('dashboard.contractors.invoice', $contractor), false)
        ->assertSee('data-sla=', false);

    $invoice = $this->actingAs($this->manager)->get(route('dashboard.contractors.invoice', $contractor));
    $invoice->assertOk();
    expect($invoice->headers->get('content-type'))->toContain('pdf');
});
