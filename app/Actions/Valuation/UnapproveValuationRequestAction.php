<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class UnapproveValuationRequestAction
{
    public function __construct(
        private readonly InvalidateValuationDashboardStatsCacheAction $invalidateCache,
    ) {}

    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('unapprove', $request);

        if (! $request->isFinallyApproved()) {
            throw new InvalidArgumentException(__('Only approved valuations can be unapproved.'));
        }

        $request->forceFill([
            'state' => 'evaluative',
            'approve' => -1,
            'ended_at' => null,
        ])->save();

        ValuationActivity::log($actor, $request, 'unapproved', 'Valuation approval revoked');
        $this->invalidateCache->execute();

        return $request->refresh();
    }
}
