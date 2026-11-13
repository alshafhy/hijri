<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Company;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationSla;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $permissions = ['dashboard.view', 'valuation_request.view', 'company.view', 'company.edit'];
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($permissions);

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
    $this->company = Company::query()->create(['name' => 'شركة', 'request_sla_hours' => 5, 'evaluation_sla_hours' => 48]);
});

function slaRequest(array $attributes): ValuationRequest
{
    return ValuationRequest::query()->create(array_merge([
        'number' => 'S-'.fake()->unique()->numberBetween(1, 99999),
        'reference' => fake()->unique()->numberBetween(1, 99999),
        'approve' => 0,
        'company_id' => test()->company->id,
    ], $attributes));
}

it('compares full elapsed hours with the stage limit', function () {
    $sla = new ValuationSla;

    expect($sla->status(slaRequest(['state' => 'request', 'started_at' => now()->subHours(4)])))->toBe(ValuationSla::ON_TIME)
        ->and($sla->status(slaRequest(['state' => 'waiting', 'started_at' => now()->subHours(6)])))->toBe(ValuationSla::OVERDUE)
        ->and($sla->status(slaRequest(['state' => 'request', 'started_at' => now()->subDays(2)->subHour()])))->toBe(ValuationSla::OVERDUE)
        ->and($sla->status(slaRequest(['state' => 'underEvaluative', 'started_at' => now()->subDays(10), 'under_evaluation_at' => now()->subHours(30)])))->toBe(ValuationSla::ON_TIME)
        ->and($sla->status(slaRequest(['state' => 'تحت التقييم', 'started_at' => now()->subDays(10), 'under_evaluation_at' => now()->subHours(50)])))->toBe(ValuationSla::OVERDUE)
        ->and($sla->status(slaRequest(['state' => 'approve', 'started_at' => now()->subDays(10)])))->toBeNull();
});

it('colours the details button in the valuations list', function () {
    slaRequest(['state' => 'request', 'started_at' => now()->subHours(1)]);
    slaRequest(['state' => 'request', 'started_at' => now()->subHours(9)]);

    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.valuation-requests.index'))
        ->assertOk()
        ->getContent();

    expect($html)->toContain('data-sla="on_time"')
        ->and($html)->toContain('data-sla="overdue"');
});

it('updates the SLA hours from the company settings', function () {
    $this->actingAs($this->manager)
        ->get(route('dashboard.companies.edit', $this->company))
        ->assertOk()
        ->assertSee('name="evaluation_sla_hours"', false);

    $this->actingAs($this->manager)
        ->put(route('dashboard.companies.sla', $this->company), ['request_sla_hours' => 12, 'evaluation_sla_hours' => 72])
        ->assertRedirect(route('dashboard.companies.edit', $this->company));

    expect($this->company->fresh())
        ->request_sla_hours->toBe(12)
        ->evaluation_sla_hours->toBe(72);

    $this->actingAs($this->manager)
        ->put(route('dashboard.companies.sla', $this->company), ['request_sla_hours' => 0, 'evaluation_sla_hours' => 72])
        ->assertSessionHasErrors('request_sla_hours');
});
