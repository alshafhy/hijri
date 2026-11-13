<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Enums\Valuation\ReportExportVariant;
use App\Models\Contractor;
use App\Models\GeoCity;
use App\Models\Property;
use App\Models\PropertyLocation;
use App\Models\PropertyTotal;
use App\Models\SystemRelease;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Services\Valuation\ValuationReportDataBuilder;
use App\Support\Valuation\StaticMapImage;
use Database\Seeders\SystemReleasesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $permissions = ['dashboard.view', 'valuation_request.view', 'contractor.view', 'contractor.edit', 'contractor.create'];
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($permissions);
    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
});

function mappedValuation(string $reference, ?GeoCity $city, float $lat, float $lng, float $amount): ValuationRequest
{
    $request = ValuationRequest::query()->create(['number' => 'M-'.$reference, 'reference' => $reference, 'state' => 'approve', 'approve' => 1]);
    $property = Property::query()->create(['valuation_request_id' => $request->id, 'customer_name' => 'عميل '.$reference, 'property_kind' => 'سكني', 'property_type' => 'فيلا']);
    PropertyLocation::query()->create(['property_id' => $property->id, 'city_id' => $city?->id, 'x_axis' => $lat, 'y_axis' => $lng]);
    PropertyTotal::query()->create(['property_id' => $property->id, 'total_amount' => $amount]);

    return $request;
}

function tilePng(): string
{
    $image = imagecreatetruecolor(256, 256);
    imagefill($image, 0, 0, (int) imagecolorallocate($image, 200, 220, 240));
    ob_start();
    imagepng($image);

    return (string) ob_get_clean();
}

it('shows all valuations on a map grouped by city', function () {
    $riyadh = GeoCity::query()->create(['legacy_id' => 1, 'name_ar' => 'الرياض', 'status' => 3]);
    $jeddah = GeoCity::query()->create(['legacy_id' => 2, 'name_ar' => 'جدة', 'status' => 3]);
    mappedValuation('5001', $riyadh, 24.71, 46.67, 1000000);
    mappedValuation('5002', $riyadh, 24.75, 46.70, 500000);
    mappedValuation('5003', $jeddah, 21.54, 39.17, 250000);
    mappedValuation('5004', $jeddah, 0, 0, 999);
    mappedValuation('5005', $jeddah, 24.70, 46.60, 1)->delete();

    $page = $this->actingAs($this->manager)->get(route('dashboard.valuation-requests.map'));
    $page->assertOk()->assertSee('valuations-map-canvas', false)->assertSee('الرياض')->assertSee('1,500,000');
    expect($page->viewData('total'))->toBe(3)
        ->and(array_column($page->viewData('cities'), 'count', 'name'))->toBe(['الرياض' => 2, 'جدة' => 1])
        ->and(array_column($page->viewData('points'), 'reference'))->toBe(['5003', '5002', '5001']);

    $filtered = $this->actingAs($this->manager)->get(route('dashboard.valuation-requests.map', ['city' => $jeddah->id]));
    expect(array_column($filtered->viewData('points'), 'reference'))->toBe(['5003']);

    $this->actingAs($this->manager)->get(route('dashboard.valuation-requests.index'))
        ->assertSee(route('dashboard.valuation-requests.map'), false);
});

it('lists system releases and links the navbar version to them', function () {
    (new SystemReleasesSeeder)->run();
    (new SystemReleasesSeeder)->run();

    $page = $this->actingAs($this->manager)->get(route('dashboard.releases'));

    $page->assertOk()->assertSee('4.0.0')->assertSee('2.0.0')->assertSee('إضافة مقارن 4 في التقرير والموقع')
        ->assertSee(route('dashboard.releases'), false);
    expect(SystemRelease::query()->count())->toBe(count(SystemReleasesSeeder::releases()));
});

it('uses the map picker on the contractor form and shows the location', function () {
    $contractor = Contractor::query()->create(['name' => 'مقاول', 'state' => 2, 'x_axis' => 24.7, 'y_axis' => 46.6]);

    $this->actingAs($this->manager)->get(route('dashboard.contractors.edit', $contractor))
        ->assertOk()->assertSee('id="contractor-map"', false)->assertSee('name="x_axis"', false);
    $this->actingAs($this->manager)->get(route('dashboard.contractors.show', $contractor))
        ->assertOk()->assertSee('data-map-view', false);
});

it('renders and caches static location maps for the PDF report', function () {
    $cleanup = fn () => array_map('unlink', glob(storage_path('app/private/report-map-cache/*-24.135791_46.975319.png')) ?: []);
    $cleanup();
    Http::fake(['tile.openstreetmap.org/*' => Http::response(tilePng()), 'server.arcgisonline.com/*' => Http::response(tilePng())]);

    $request = mappedValuation('6001', null, 24.135791, 46.975319, 100);
    $data = app(ValuationReportDataBuilder::class)->build($request, ReportExportVariant::Full);

    expect(array_keys($data['location_maps']))->toBe(['streets', 'aerial'])
        ->and($data['location_maps']['streets'])->toStartWith('data:image/png;base64,');

    $png = imagecreatefromstring(base64_decode(substr($data['location_maps']['streets'], 22)));
    expect(imagesx($png))->toBe(640)->and(imagesy($png))->toBe(360);

    $requests = Http::recorded()->count();
    app(ValuationReportDataBuilder::class)->build($request, ReportExportVariant::Full);
    expect(Http::recorded()->count())->toBe($requests);

    $html = view('pdf.report.full', $data)->render();
    expect($html)->toContain(__('Property location'));

    $cleanup();
});

it('skips the location maps when tiles are unavailable or the point is empty', function () {
    Http::fake(['*' => Http::response('', 503)]);

    expect((new StaticMapImage)->dataUri(24.7, 46.6, StaticMapImage::STREETS, 15, 300, 200))->toBeNull()
        ->and((new StaticMapImage)->dataUri(0, 0, StaticMapImage::STREETS))->toBeNull()
        ->and(StaticMapImage::isValidPoint('abc', 46))->toBeFalse();
});
