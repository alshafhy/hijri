<?php

declare(strict_types=1);

use App\Models\SystemComponent;
use App\Models\User;
use Database\Seeders\PermissionsSeeder;
use Database\Seeders\SystemComponentsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(PermissionsSeeder::class);
    $this->seed(SystemComponentsSeeder::class);

    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
});

it('translates sidebar labels when the locale switches to english', function () {
    $arabic = $this->actingAs($this->user)
        ->withSession(['locale' => 'ar', 'direction' => 'rtl'])
        ->get(route('home'))
        ->assertOk()
        ->getContent();

    $english = $this->actingAs($this->user)
        ->withSession(['locale' => 'en', 'direction' => 'ltr'])
        ->get(route('home'))
        ->assertOk()
        ->getContent();

    expect($arabic)->toContain('طلبات التقييم')
        ->and($english)->toContain('Valuation Requests')
        ->and($english)->not->toContain('طلبات التقييم');
});

it('resolves a system component label from the current locale', function () {
    $item = SystemComponent::query()->where('comp_name', 'valuation_requests')->firstOrFail();

    app()->setLocale('ar');
    expect($item->label())->toBe('طلبات التقييم');

    app()->setLocale('en');
    expect($item->label())->toBe('Valuation Requests');
});
