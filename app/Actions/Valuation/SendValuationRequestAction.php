<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;
use InvalidArgumentException;

final class SendValuationRequestAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('send', $request);

        $state = mb_strtolower(trim((string) $request->state));

        if (! in_array($state, ['waiting', 'request', 'waiting2'], true)) {
            throw new InvalidArgumentException(__('Cannot send valuation in the current state.'));
        }

        if ($state === 'waiting') {
            $newState = $request->evaluator_user_id ? 'underEvaluative' : 'request';
            $payload = ['state' => $newState];
            if ($newState === 'underEvaluative') {
                $payload['under_evaluation_at'] = now();
            }
            $request->forceFill($payload)->save();
            ValuationActivity::log($actor, $request, 'sent', 'Valuation request sent', ['state' => $newState]);

            return $request->refresh();
        }

        if ($request->evaluator_user_id === null && $state === 'request') {
            throw new InvalidArgumentException(__('An evaluator must be assigned before sending.'));
        }

        $request->forceFill([
            'state' => 'underEvaluative',
            'under_evaluation_at' => now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'under_evaluation', 'Valuation moved under evaluation');

        return $request->refresh();
    }
}
