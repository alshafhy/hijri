<?php

declare(strict_types=1);

use App\Enums\Valuation\RequestState;
use App\Models\Company;
use App\Models\ContactMessage;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\GeoNeighborhood;
use App\Models\ImportRun;
use App\Models\Offer;
use App\Models\OfferEstate;
use App\Models\Partner;
use App\Models\PartnerContract;
use App\Models\PartyContact;
use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Models\PropertyComponent;
use App\Models\PropertyPicture;
use App\Models\PropertyTotal;
use App\Models\ServiceContract;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;
use Database\Seeders\BranchSeeder;
use Database\Seeders\DemoDataSeeder;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\PermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds demo staff, commercial records, and eight sample valuation requests', function () {
    $this->seed([
        BranchSeeder::class,
        PermissionsSeeder::class,
        DemoUserSeeder::class,
        DemoDataSeeder::class,
    ]);

    $manager = User::query()->where('username', config('users.demo.manager.username'))->first();
    $coordinator = User::query()->where('username', config('users.demo.coordinator.username'))->first();
    $evaluator = User::query()->where('username', config('users.demo.evaluator.username'))->first();

    expect($manager)->not->toBeNull()
        ->and($manager->hasRole('manager'))->toBeTrue()
        ->and($manager->can_approve_final_request)->toBeTrue()
        ->and($coordinator)->not->toBeNull()
        ->and($coordinator->hasRole('coordinator'))->toBeTrue()
        ->and($evaluator)->not->toBeNull()
        ->and($evaluator->hasRole('evaluator'))->toBeTrue()
        ->and(Company::query()->count())->toBeGreaterThan(0)
        ->and(GeoCity::query()->count())->toBeGreaterThan(0)
        ->and(GeoNeighborhood::query()->count())->toBeGreaterThan(0)
        ->and(Partner::query()->count())->toBeGreaterThan(0)
        ->and(Contractor::query()->count())->toBeGreaterThan(0)
        ->and(PartyContact::query()->count())->toBeGreaterThan(0)
        ->and(Offer::query()->count())->toBeGreaterThan(0)
        ->and(OfferEstate::query()->count())->toBeGreaterThan(0)
        ->and(Contract::query()->count())->toBeGreaterThan(0)
        ->and(PartnerContract::query()->count())->toBeGreaterThan(0)
        ->and(ServiceContract::query()->count())->toBeGreaterThan(0)
        ->and(ContactMessage::query()->count())->toBeGreaterThan(0)
        ->and(SupportRequest::query()->count())->toBeGreaterThan(0)
        ->and(ImportRun::query()->count())->toBeGreaterThan(0)
        ->and(ValuationRequest::withTrashed()->count())->toBe(8)
        ->and(ValuationRequest::withTrashed()->where('state', RequestState::Approve->value)->exists())->toBeTrue()
        ->and(ValuationRequest::withTrashed()->where('state', RequestState::Cancelled->value)->onlyTrashed()->exists())->toBeTrue()
        ->and(PropertyComparable::query()->count())->toBeGreaterThan(0)
        ->and(PropertyAdjustment::query()->count())->toBeGreaterThan(0)
        ->and(PropertyComponent::query()->count())->toBeGreaterThan(0)
        ->and(PropertyTotal::query()->whereNotNull('total_amount_manual')->exists())->toBeTrue()
        ->and(ValuationCustomTableRow::query()->count())->toBeGreaterThan(0)
        ->and(ValuationAttachment::query()->count())->toBeGreaterThan(0)
        ->and(PropertyPicture::query()->count())->toBeGreaterThan(0);
});
