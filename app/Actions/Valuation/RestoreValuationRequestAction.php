<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class RestoreValuationRequestAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('restore', $request);

        $request->restore();
        if ($request->state === 'cancelled') {
            $request->forceFill(['state' => 'waiting'])->save();
        }

        ValuationActivity::log($actor, $request, 'restored', 'Valuation restored from soft-delete');

        return $request->refresh();
    }
}
