<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class RejectValuationRequestAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('reject', $request);

        $request->forceFill([
            'state' => 'تحت التقييم',
            'evaluated_at' => null,
        ])->save();

        ValuationActivity::log($actor, $request, 'rejected', 'Valuation rejected back to evaluator');

        return $request->refresh();
    }
}
