<?php

declare(strict_types=1);

use App\Enums\UserStatus;
use App\Mail\StaffInvitationMail;
use App\Models\Contract;
use App\Models\Contractor;
use App\Models\RequestFeeShare;
use App\Models\SupportRequest;
use App\Models\User;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    $managerPermissions = ['dashboard.view', 'user.view', 'user.create', 'user.edit', 'valuation_request.view', 'valuation_request.approve_final'];
    foreach ([...$managerPermissions, 'valuation_request.mark_evaluated'] as $permission) {
        Permission::findOrCreate($permission, 'web');
    }
    Role::findOrCreate('manager', 'web')->syncPermissions($managerPermissions);
    Role::findOrCreate('evaluator', 'web')->syncPermissions(['dashboard.view', 'valuation_request.view', 'valuation_request.mark_evaluated']);
    Role::findOrCreate('coordinator', 'web')->syncPermissions(['dashboard.view', 'valuation_request.view']);
    Mail::fake();
    Storage::fake('local');

    $this->manager = User::factory()->create(['status' => UserStatus::Active]);
    $this->manager->assignRole('manager');
});

function inviteToken(): string
{
    $token = null;
    Mail::assertSent(StaffInvitationMail::class, function (StaffInvitationMail $mail) use (&$token) {
        $token = basename(parse_url($mail->activationUrl, PHP_URL_PATH));

        return true;
    });

    return $token;
}

it('invites a staff member who activates the account from the emailed link', function () {
    $this->actingAs($this->manager)
        ->post(route('dashboard.users.invite'), ['role' => 'evaluator', 'name' => 'مقيم جديد', 'email' => 'new.eval@example.com', 'phone_number' => '0500000000'])
        ->assertRedirect();

    $member = User::query()->where('email', 'new.eval@example.com')->firstOrFail();
    expect($member->status)->toBe(UserStatus::Inactive)
        ->and($member->hasRole('evaluator'))->toBeTrue()
        ->and($member->hasPendingInvitation())->toBeTrue()
        ->and($member->username)->toBe('new.eval');

    $token = inviteToken();
    expect($member->invitation_token)->toBe(hash('sha256', $token));

    auth()->logout();

    $this->get(route('invitations.show', $token))->assertOk()->assertSee('new.eval');
    $this->post(route('invitations.accept', $token), ['password' => 'Secret123', 'password_confirmation' => 'Secret123'])
        ->assertRedirect(route('home'));

    $member->refresh();
    expect($member->status)->toBe(UserStatus::Active)
        ->and($member->hasPendingInvitation())->toBeFalse();
    $this->assertAuthenticatedAs($member);

    auth()->logout();
    $this->get(route('invitations.show', $token))->assertOk()->assertSee(__('This invitation link is invalid or has expired. Ask your manager to send a new one.'));
});

it('rejects expired invitations and enforces the staff limit', function () {
    config(['users.staff_limit' => 1]);

    $this->actingAs($this->manager)
        ->post(route('dashboard.users.invite'), ['role' => 'coordinator', 'name' => 'منسق', 'email' => 'coord@example.com'])
        ->assertRedirect();
    $token = inviteToken();

    $this->actingAs($this->manager)
        ->post(route('dashboard.users.invite'), ['role' => 'evaluator', 'name' => 'آخر', 'email' => 'other@example.com'])
        ->assertSessionHasErrors('email');
    expect(User::query()->where('email', 'other@example.com')->exists())->toBeFalse();

    auth()->logout();
    $this->travel(73)->hours();
    $this->post(route('invitations.accept', $token), ['password' => 'Secret123', 'password_confirmation' => 'Secret123'])->assertNotFound();
});

it('delegates final approval, which unlocks approving for the staff member', function () {
    $evaluator = User::factory()->create(['status' => UserStatus::Active]);
    $evaluator->assignRole('evaluator');
    $request = ValuationRequest::query()->create(['number' => 'D-1', 'reference' => 501, 'state' => 'تم التقييم', 'approve' => 0, 'evaluator_user_id' => $evaluator->id]);

    expect($evaluator->can('approveFinal', $request))->toBeFalse();

    $this->actingAs($this->manager)
        ->put(route('dashboard.users.final-approval', $evaluator), ['granted' => 1])
        ->assertRedirect();
    expect($evaluator->fresh()->can('approveFinal', $request))->toBeTrue();

    $this->actingAs($evaluator->fresh())->get(route('home'))->assertSee('final-approval-badge', false);

    $this->actingAs($this->manager)->put(route('dashboard.users.final-approval', $evaluator), ['granted' => 0]);
    expect($evaluator->fresh()->can('approveFinal', $request))->toBeFalse();

    $this->actingAs($this->manager)
        ->put(route('dashboard.users.final-approval', $this->manager), ['granted' => 1])
        ->assertSessionHasErrors('member');
});

it('deactivates staff members, which blocks their login', function () {
    $coordinator = User::factory()->create(['status' => UserStatus::Active, 'username' => 'coord1', 'password' => 'Secret123']);
    $coordinator->assignRole('coordinator');

    $this->actingAs($this->manager)->put(route('dashboard.users.status', $coordinator), ['active' => 0])->assertRedirect();
    expect($coordinator->fresh()->status)->toBe(UserStatus::Inactive);

    auth()->logout();
    $this->post(route('login'), ['username' => 'coord1', 'password' => 'Secret123'])->assertSessionHasErrors();
    $this->assertGuest();
});

it('shows the staff profile with counters, monthly chart and dues', function () {
    $evaluator = User::factory()->create(['status' => UserStatus::Active, 'membership_number' => 'M-77']);
    $evaluator->assignRole('evaluator');
    $contractor = Contractor::query()->create(['name' => 'مقاول', 'fees' => 2000]);
    foreach ([['approve', 1], ['underEvaluative', 0]] as $i => [$state, $approve]) {
        $request = ValuationRequest::query()->create([
            'number' => 'P-'.$i, 'reference' => 600 + $i, 'state' => $state, 'approve' => $approve,
            'evaluator_user_id' => $evaluator->id, 'coordinator_user_id' => $this->manager->id, 'started_at' => now(),
        ]);
        RequestFeeShare::query()->create(['valuation_request_id' => $request->id, 'coordinator_share' => 10, 'evaluator_share' => 25, 'manager_share' => 65]);
        Contract::query()->create(['contractor_id' => $contractor->id, 'valuation_request_id' => $request->id, 'state' => 0]);
    }

    $html = $this->actingAs($this->manager)
        ->get(route('dashboard.users.show', $evaluator))
        ->assertOk()
        ->assertSee('M-77')
        ->assertSee('staff-monthly-chart', false)
        ->getContent();

    expect($html)->toContain('data-counter="total">2<')
        ->and($html)->toContain('data-counter="approved">1<')
        ->and($html)->toContain('data-counter="under_evaluation">1<')
        ->and($html)->toContain('data-dues-total>1,000.00<');

    $this->actingAs($this->manager)
        ->put(route('dashboard.users.membership', $evaluator), ['membership_number' => 'M-88'])
        ->assertRedirect();
    expect($evaluator->fresh()->membership_number)->toBe('M-88');
});

it('lets users edit their profile, upload a signature and contact support', function () {
    $this->actingAs($this->manager)->get(route('dashboard.profile.edit'))->assertOk();

    $this->actingAs($this->manager)
        ->put(route('dashboard.profile.update'), [
            'name' => 'مدير', 'email' => 'manager@example.com', 'phone_number' => '0555',
            'membership_number' => 'X-1', 'membership_category' => 'زميل', 'membership_branch_type' => 'عقار',
        ])
        ->assertRedirect(route('dashboard.profile.edit'));
    expect($this->manager->fresh())->membership_category->toBe('زميل')->email->toBe('manager@example.com');

    $this->actingAs($this->manager)
        ->post(route('dashboard.profile.images.upload', 'signature'), ['image' => UploadedFile::fake()->image('sig.png')])
        ->assertRedirect();
    $path = $this->manager->fresh()->signature_path;
    Storage::disk('local')->assertExists($path);
    $this->actingAs($this->manager)->get(route('dashboard.profile.images.show', [$this->manager, 'signature']))->assertOk();

    $other = User::factory()->create();
    $this->actingAs($other)->get(route('dashboard.profile.images.show', [$this->manager, 'signature']))->assertForbidden();

    $this->actingAs($this->manager)
        ->post(route('dashboard.profile.support'), ['title' => 'مشكلة', 'body' => 'تفاصيل المشكلة'])
        ->assertRedirect(route('dashboard.profile.edit', ['tab' => 'help']));
    $support = SupportRequest::query()->sole();
    expect($support->user_id)->toBe($this->manager->id)->and($support->status)->toBe('open');

    $this->actingAs($this->manager)->get(route('dashboard.support-requests.index'))->assertOk()->assertSee('تفاصيل المشكلة');
    $this->actingAs($this->manager)->put(route('dashboard.support-requests.update', $support), ['status' => 'closed'])->assertRedirect();
    expect($support->fresh()->status)->toBe('closed');
});
