<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Property;
use App\Models\PropertyComponent;
use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationCustomTableRow;
use App\Models\ValuationRequest;
use App\Support\Valuation\ArabicAmountInWords;
use App\Support\Valuation\ValuationBreakdown;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $all = [
        'dashboard.view', 'valuation_request.view', 'valuation_request.create', 'valuation_request.edit',
        'valuation_request.send', 'valuation_request.cancel', 'valuation_request.change_evaluator',
        'valuation_request.change_coordinator', 'valuation_request.change_property_type',
        'valuation_request.override_amount', 'valuation_request.manage_fee_shares', 'valuation_request.qima_upload',
        'valuation_request.export_pdf', 'valuation_request.approve_final', 'valuation_request.mark_evaluated',
    ];
    foreach ($all as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($all);
    Role::findOrCreate('evaluator', 'web')->syncPermissions(['dashboard.view', 'valuation_request.view', 'valuation_request.edit', 'valuation_request.mark_evaluated']);
    app()->setLocale('ar');
    Storage::fake('local');

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
    $this->evaluator = User::factory()->create(['status' => UserStatus::Active]);
    $this->evaluator->assignRole('evaluator');

    $this->request = ValuationRequest::query()->create([
        'number' => 'T-100', 'reference' => 10100, 'state' => 'underEvaluative', 'approve' => 0,
        'coordinator_user_id' => $this->manager->id, 'evaluator_user_id' => $this->evaluator->id,
        'started_at' => now()->subDays(3), 'under_evaluation_at' => now()->subDays(1),
    ]);
    $this->property = Property::query()->create([
        'valuation_request_id' => $this->request->id,
        'customer_name' => 'عميل', 'owner_name' => 'مالك المعاينة',
        'property_kind' => 'سكني', 'property_type' => 'فيلا', 'instrument_no' => 'DEED-9',
    ]);
    PropertyComponent::query()->create(['property_id' => $this->property->id, 'component_key' => 'land_area', 'area_value' => 400, 'price_value' => 1000]);
    PropertyComponent::query()->create(['property_id' => $this->property->id, 'component_key' => 'floor_g', 'area_value' => 200, 'price_value' => 2000]);
    PropertyComponent::query()->create(['property_id' => $this->property->id, 'component_key' => 'pool', 'area_value' => 0]);
    PropertyTotal::query()->create(['property_id' => $this->property->id, 'total_amount' => 800000, 'profit_amount' => 0, 'depreciation_amount' => 0]);
    ValuationCustomTableRow::query()->create(['valuation_request_id' => $this->request->id, 'table_title' => 'جدول الإيجارات', 'label' => 'الدور الأول', 'values' => ['5000', 'سنوي'], 'sort_order' => 0]);
    ValuationAttachment::query()->create(['valuation_request_id' => $this->request->id, 'type' => 'صورة للصك', 'original_name' => 'deed.pdf', 'path' => 'x/deed.pdf', 'disk' => 'local']);
});

it('summarises the valuation grid with land and building shares', function () {
    $breakdown = ValuationBreakdown::forProperty($this->property->load('components'));

    expect(array_column($breakdown['rows'], 'key'))->toBe(['land_area', 'floor_g'])
        ->and($breakdown['rows'][0]['title'])->toBe('مساحة الأرض')
        ->and($breakdown['total'])->toBe(800000.0)
        ->and($breakdown['land_share'])->toBe(50.0)
        ->and($breakdown['building_share'])->toBe(50.0);
});

it('renders every detail section and the manager toolbar', function () {
    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.valuation-requests.show', $this->request))
        ->assertOk()
        ->assertSee('مالك المعاينة')
        ->assertSee('DEED-9')
        ->assertSee('جدول الإيجارات')
        ->assertSee('deed.pdf')
        ->assertSee(app(ArabicAmountInWords::class)->convert(800000), false)
        ->getContent();

    foreach (['#changeEvaluatorModal', '#changePropertyTypeModal', '#manualAmountModal', '#sharesModal'] as $modal) {
        expect($html)->toContain('data-bs-target="'.$modal.'"');
    }
    foreach (['#approveModal', '#cancelModal', '#officialReportModal', '#unapproveModal'] as $modal) {
        expect($html)->not->toContain('data-bs-target="'.$modal.'"');
    }
});

it('follows the workflow stage matrix for the manager toolbar', function (string $state, array $visible, array $hidden) {
    $this->request->forceFill(['state' => $state])->save();

    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.valuation-requests.show', $this->request))
        ->assertOk()
        ->getContent();

    foreach ($visible as $modal) {
        expect($html)->toContain('data-bs-target="#'.$modal.'"');
    }
    foreach ($hidden as $modal) {
        expect($html)->not->toContain('data-bs-target="#'.$modal.'"');
    }
})->with([
    'waiting' => ['waiting', ['sendModal', 'cancelModal', 'officialReportModal'], ['approveModal', 'rejectModal']],
    'evaluated' => ['تم التقييم', ['approveModal'], ['sendModal', 'cancelModal', 'changeEvaluatorModal']],
]);

it('limits the evaluator toolbar to evaluator actions', function () {
    $html = $this->actingAs($this->evaluator)
        ->get(route('dashboard.valuation-requests.show', $this->request))
        ->assertOk()
        ->getContent();

    expect($html)->toContain(route('dashboard.valuation-requests.edit-info', $this->request))
        ->and($html)->toContain(route('dashboard.valuation-requests.mark-evaluated', $this->request))
        ->and($html)->not->toContain(route('dashboard.valuation-requests.edit', $this->request).'"')
        ->and($html)->not->toContain('data-bs-target="#changeEvaluatorModal"')
        ->and($html)->not->toContain('data-bs-target="#cancelModal"');
});

it('shows only a disabled button for cancelled requests', function () {
    $this->request->forceFill(['state' => 'delete'])->save();

    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.valuation-requests.show', $this->request))
        ->assertOk()
        ->getContent();

    expect($html)->toContain(__('This valuation has been cancelled'))
        ->and($html)->not->toContain('data-bs-target="#sendModal"');
});

it('stores report and deposit numbers with the official Qima report attachment', function () {
    $this->actingAs($this->manager)
        ->post(route('dashboard.valuation-requests.official-report', $this->request), [
            'official_report' => UploadedFile::fake()->create('qima.pdf', 30, 'application/pdf'),
            'report_number' => 'Q-555',
            'deposit_number' => 'DEP-777',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $fresh = $this->request->fresh();
    expect($fresh->number)->toBe('Q-555')
        ->and($fresh->deposit_number)->toBe('DEP-777')
        ->and($fresh->uploaded_on_qima)->toBeTrue()
        ->and(ValuationAttachment::query()->where('type', 'التقرير الرسمي')->where('valuation_request_id', $fresh->id)->exists())->toBeTrue();
});
