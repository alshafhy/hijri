<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class ChangeCoordinatorAction
{
    public function execute(User $actor, ValuationRequest $request, int $coordinatorUserId): ValuationRequest
    {
        Gate::forUser($actor)->authorize('changeCoordinator', $request);

        $previous = $request->coordinator_user_id;
        $request->forceFill([
            'coordinator_user_id' => $coordinatorUserId,
        ])->save();

        ValuationActivity::log($actor, $request, 'coordinator_changed', 'Coordinator changed', [
            'from' => $previous,
            'to' => $coordinatorUserId,
        ]);

        return $request->refresh();
    }
}
