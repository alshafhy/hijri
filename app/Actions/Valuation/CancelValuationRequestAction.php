<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class CancelValuationRequestAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('cancel', $request);

        ValuationActivity::log($actor, $request, 'cancelled', 'Valuation soft-deleted (cancelled)');

        $request->forceFill(['state' => 'cancelled'])->save();
        $request->delete();

        return $request;
    }
}
