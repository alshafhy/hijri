<?php

declare(strict_types=1);

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    Permission::findOrCreate('company.view', 'web');
    Permission::findOrCreate('company.edit', 'web');
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(['company.view', 'company.edit']);
    $this->company = Company::query()->create(['name' => 'شركة']);
});

it('uploads a separate report logo', function () {
    Storage::fake('public');

    $this->actingAs($this->user)
        ->post(route('dashboard.companies.branding', $this->company), ['type' => 'report_logo', 'image' => UploadedFile::fake()->image('r.png')])
        ->assertRedirect();

    expect($this->company->fresh()->report_logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($this->company->fresh()->report_logo_path);
});

it('saves accreditation dates on the company profile and rejects an end date before the membership date', function () {
    $payload = ['name' => 'شركة', 'membership_date' => '2017-01-01', 'accreditation_end_date' => '2027-09-15'];
    $this->actingAs($this->user)->put(route('dashboard.companies.profile', $this->company), $payload)->assertRedirect();
    expect($this->company->fresh()->accreditation_end_date->format('Y-m-d'))->toBe('2027-09-15');

    $this->actingAs($this->user)
        ->put(route('dashboard.companies.profile', $this->company), [...$payload, 'accreditation_end_date' => '2016-01-01'])
        ->assertSessionHasErrors('accreditation_end_date');
});

it('renders the report logo upload and accreditation date inputs on the edit page', function () {
    Storage::fake('public');
    $this->company->forceFill([
        'report_logo_path' => UploadedFile::fake()->image('r.png')->store('company/1/report_logo', 'public'),
    ])->save();

    $this->actingAs($this->user)
        ->get(route('dashboard.companies.edit', $this->company))
        ->assertOk()
        ->assertSee('name="membership_date"', false)
        ->assertSee('name="accreditation_end_date"', false)
        ->assertSee('value="report_logo"', false)
        ->assertSee('src="data:image/png;base64,', false)
        ->assertDontSee('company/1/report_logo', false);
});
