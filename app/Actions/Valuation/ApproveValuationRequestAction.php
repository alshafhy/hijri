<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class ApproveValuationRequestAction
{
    public function __construct(
        private readonly InvalidateValuationDashboardStatsCacheAction $invalidateCache,
    ) {}

    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('approveFinal', $request);

        $state = (string) $request->state;
        if (! in_array($state, ['تم التقييم', 'evaluative'], true)) {
            throw new InvalidArgumentException(__('Valuation can only be approved after evaluation is complete.'));
        }

        $request->forceFill([
            'approve' => 1,
            'state' => 'approve',
            'ended_at' => now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'approved', 'Valuation finally approved');
        $this->invalidateCache->execute();

        return $request->refresh();
    }
}
