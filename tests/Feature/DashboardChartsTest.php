<?php

declare(strict_types=1);

use App\Actions\Valuation\BuildDashboardChartsAction;
use App\Models\ValuationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('groups valuation requests by month and state for dashboard charts', function () {
    ValuationRequest::query()->create(['number' => 'C-1', 'reference' => 1, 'state' => 'waiting']);
    ValuationRequest::query()->create(['number' => 'C-2', 'reference' => 2, 'state' => 'waiting']);
    ValuationRequest::query()->create(['number' => 'C-3', 'reference' => 3, 'state' => 'approve']);
    ValuationRequest::query()->create(['number' => 'C-4', 'reference' => 4, 'state' => null]);

    $charts = app(BuildDashboardChartsAction::class)->execute();

    expect($charts['monthly']['labels'])->toHaveCount(12)
        ->and(array_sum($charts['monthly']['values']))->toBe(4)
        ->and($charts['by_state']['values'])->toContain(2)
        ->and($charts['by_state']['values'])->toContain(1)
        ->and($charts['by_state']['labels'])->toHaveCount(3);
});

it('groups dashboard state counts by the state column for mysql only_full_group_by', function () {
    $sql = strtolower(ValuationRequest::query()
        ->select('state')
        ->selectRaw('COUNT(*) as aggregate')
        ->groupBy('state')
        ->toSql());

    expect($sql)->toMatch('/group by [`"]state[`"]/')
        ->and($sql)->not->toContain('coalesce');
});
