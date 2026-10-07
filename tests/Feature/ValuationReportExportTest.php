<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Enums\Valuation\ReportExportVariant;
use App\Models\Property;
use App\Models\PropertyPicture;
use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportPdfGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();

    foreach ([
        'valuation_request.view',
        'valuation_request.export_pdf',
    ] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }

    Role::findOrCreate('manager', 'web')->givePermissionTo([
        'valuation_request.view',
        'valuation_request.export_pdf',
    ]);

    config([
        'report_pdf.enabled' => true,
        'report_pdf.queue' => false,
        'activitylog.enabled' => true,
    ]);
});

function makeExportFixtures(): array
{
    $manager = User::factory()->create(['status' => UserStatus::Active]);
    $manager->assignRole('manager');

    $request = ValuationRequest::query()->create([
        'legacy_id' => 920001,
        'reference' => 920001,
        'number' => 'R-920001',
        'state' => 'approve',
        'approve' => 1,
    ]);

    $property = Property::query()->create([
        'legacy_id' => 920001,
        'valuation_request_id' => $request->id,
        'customer_name' => 'عميل تجريبي',
        'owner_name' => 'مالك تجريبي',
        'property_kind' => 'سكني',
        'property_type' => 'فيلا',
        'value_assumption' => 4,
    ]);

    PropertyTotal::query()->create([
        'legacy_id' => 920001,
        'property_id' => $property->id,
        'total_amount' => 1000000,
        'total_amount_manual' => 1250000,
        'forced_sale_percentage' => 15,
        'forced_sale_amount' => 187500,
        'total_area' => 420,
    ]);

    PropertyPicture::query()->create([
        'legacy_id' => 920001,
        'property_id' => $property->id,
        'filename' => 'missing-for-pdf.jpg',
        'description' => 'واجهة',
        'file_exists' => false,
        'relative_path' => null,
        'sort_order' => 0,
    ]);

    return [$manager, $request->fresh(['property.total', 'property.pictures'])];
}

it('queues and downloads each export variant with manual final amount', function (string $variant) {
    [$manager, $request] = makeExportFixtures();

    $this->actingAs($manager)
        ->getJson(route('dashboard.valuation-requests.exports.queue', [$request, $variant]))
        ->assertOk()
        ->assertJsonPath('ready', true)
        ->assertJsonStructure(['key', 'download_url', 'status_url', 'filename']);

    $payload = $this->actingAs($manager)
        ->getJson(route('dashboard.valuation-requests.exports.queue', [$request, $variant]))
        ->json();

    $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.exports.status', [
            'valuationRequest' => $request,
            'variant' => $variant,
            'key' => $payload['key'],
        ]))
        ->assertOk()
        ->assertJsonPath('ready', true);

    $download = $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.exports.download', [
            'valuationRequest' => $request,
            'variant' => $variant,
            'key' => $payload['key'],
        ]));

    $download->assertOk();
    expect($download->headers->get('content-type'))->toContain('pdf');
    expect(strlen($download->getContent()))->toBeGreaterThan(500);

    $html = app(ValuationReportPdfGenerator::class)
        ->prepare($request, ReportExportVariant::from($variant))['html'];

    expect($html)->toContain('1,250,000')
        ->and($html)->toContain('15')
        ->and($html)->toContain('واجهة')
        ->and($html)->not->toContain('missing-for-pdf.jpg');

    if ($variant === 'full-draft') {
        expect($html)->toContain('مسودة');
    }

    expect(Activity::query()->where('log_name', 'valuation_report')->count())->toBeGreaterThan(0);
})->with([
    'enforcement',
    'full',
    'full-draft',
]);

it('forbids export without permission', function () {
    Permission::findOrCreate('valuation_request.view', 'web');
    $user = User::factory()->create(['status' => UserStatus::Active]);
    $user->givePermissionTo('valuation_request.view');

    $request = ValuationRequest::query()->create([
        'legacy_id' => 920002,
        'number' => 'R-920002',
        'state' => 'waiting',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard.valuation-requests.exports.queue', [$request, 'full']))
        ->assertForbidden();
});
