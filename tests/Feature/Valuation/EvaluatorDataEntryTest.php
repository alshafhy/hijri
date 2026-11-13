<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Models\Property;
use App\Models\PropertyAdjustment;
use App\Models\PropertyComparable;
use App\Models\User;
use App\Models\ValuationAttachment;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $permissions = ['dashboard.view', 'valuation_request.view', 'valuation_request.edit'];
    foreach ($permissions as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($permissions);
    app()->setLocale('ar');
    Storage::fake('local');
});

function evaluatorDossier(): array
{
    $manager = User::factory()->create(['status' => UserStatus::Active]);
    $manager->assignRole('manager');

    $request = ValuationRequest::query()->create([
        'number' => 'R-EDIT-1',
        'reference' => 99001,
        'state' => 'underEvaluative',
        'approve' => 0,
        'coordinator_user_id' => $manager->id,
        'evaluator_user_id' => $manager->id,
    ]);
    $property = Property::query()->create([
        'valuation_request_id' => $request->id,
        'customer_name' => 'عميل',
        'property_kind' => 'سكني',
        'property_type' => 'فيلا',
    ]);

    return [$manager, $request, $property];
}

it('renders every evaluator tab for a building', function () {
    [$manager, $request] = evaluatorDossier();

    $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.edit-info', $request))
        ->assertOk()
        ->assertSee('components[land_area][area_value]', false)
        ->assertSee('comparables[4][price]', false)
        ->assertSee('custom_table[title]', false)
        ->assertSee('new_attachments[0][file]', false)
        ->assertSee('building[default_build_age]', false);
});

it('saves the full evaluator dossier and recalculates totals', function () {
    [$manager, $request, $property] = evaluatorDossier();

    $this->actingAs($manager)
        ->put(route('dashboard.valuation-requests.update-info', $request), [
            'sections' => ['land', 'facades', 'services', 'question', 'components', 'totals', 'comparables', 'custom_table', 'flooring', 'building', 'ac'],
            'owner_name' => 'مالك جديد',
            'base_value' => '1',
            'land' => ['street' => ['تجاري', 'سكني']],
            'building' => ['build_age' => '10', 'default_build_age' => 50, 'remaining_build_age' => 40],
            'question' => ['populated' => 'نعم', 'need_cost' => 'لا', 'best_use' => 1],
            'components' => [
                'land_area' => ['area_value' => 500, 'price_value' => 1000],
                'floor_g' => ['area_value' => 300, 'price_value' => 1500],
            ],
            'totals' => ['profit_percentage' => 10, 'depreciation_type' => 1, 'movables' => 0],
            'comparables' => [
                1 => ['area' => 400, 'price' => 1000, 'market_conditions_percentage' => 10, 'proportional_adjustment' => 100],
                2 => ['area' => null, 'price' => null],
            ],
            'meter_price_approximately' => 1100,
            'comparison_information_source' => 'منصة السوق',
            'custom_table' => ['title' => 'جدول', 'rows' => [['label' => 'صف', 'values' => ['أ', 'ب']]]],
            'new_pictures' => [UploadedFile::fake()->image('front.jpg')],
            'new_picture_descriptions' => ['واجهة'],
            'new_attachments' => [['file' => UploadedFile::fake()->create('deed.pdf', 50, 'application/pdf'), 'type' => 'صورة للصك']],
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    $property->refresh()->load(['total', 'building', 'question', 'lands', 'pictures']);
    // total 950,000; land 500,000; profit 45,000; depreciation 10/50 × 495,000 = 99,000
    expect($property->owner_name)->toBe('مالك جديد')
        ->and((float) $property->total->total_amount)->toBe(896_000.0)
        ->and($property->building->default_build_age)->toBe(50)
        ->and($property->question->populated)->toBe('نعم')
        ->and($property->lands->first()->street)->toBe('تجاري,سكني')
        ->and($property->pictures)->toHaveCount(1);

    Storage::disk('local')->assertExists($property->pictures->first()->relative_path);

    expect(PropertyComparable::query()->where('property_id', $property->id)->count())->toBe(1);
    $adjustment = PropertyAdjustment::query()->where('property_id', $property->id)->where('type', 1)->first();
    expect((float) $adjustment->meter_price)->toBe(1100.0)
        ->and((float) $adjustment->meter_price_approximately)->toBe(1100.0);

    expect($request->customTableRows()->first()->values)->toBe(['أ', 'ب']);

    $attachment = ValuationAttachment::query()->where('valuation_request_id', $request->id)->firstOrFail();
    expect($attachment->type)->toBe('صورة للصك');

    $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.attachments.download', [$request, $attachment]))
        ->assertOk();

    $this->actingAs($manager)
        ->delete(route('dashboard.valuation-requests.attachments.destroy', [$request, $attachment]))
        ->assertRedirect();

    expect(ValuationAttachment::query()->whereKey($attachment->id)->exists())->toBeFalse();
    Storage::disk('local')->assertMissing($attachment->path);
});

it('rejects attachments that belong to another request', function () {
    [$manager, $request] = evaluatorDossier();
    $other = ValuationRequest::query()->create(['number' => 'R-OTHER', 'reference' => 99002, 'state' => 'waiting', 'approve' => 0]);
    $attachment = ValuationAttachment::query()->create([
        'valuation_request_id' => $other->id,
        'original_name' => 'x.pdf',
        'path' => 'valuation-attachments/x.pdf',
        'disk' => 'local',
    ]);

    $this->actingAs($manager)
        ->get(route('dashboard.valuation-requests.attachments.download', [$request, $attachment]))
        ->assertNotFound();
});
