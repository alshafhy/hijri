<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class MarkEvaluatedAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('markEvaluated', $request);

        $state = (string) $request->state;
        if (! in_array($state, ['underEvaluative', 'تحت التقييم'], true)) {
            throw new InvalidArgumentException(__('Current state does not allow marking as evaluated.'));
        }

        $request->forceFill([
            'state' => 'تم التقييم',
            'evaluated_at' => now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'evaluated', 'Valuation marked as evaluated');

        return $request->refresh();
    }
}
