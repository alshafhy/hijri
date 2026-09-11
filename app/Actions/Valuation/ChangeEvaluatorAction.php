<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class ChangeEvaluatorAction
{
    public function execute(User $actor, ValuationRequest $request, int $evaluatorUserId): ValuationRequest
    {
        Gate::forUser($actor)->authorize('changeEvaluator', $request);

        $previous = $request->evaluator_user_id;
        $request->forceFill([
            'evaluator_user_id' => $evaluatorUserId,
            'state' => 'underEvaluative',
            'under_evaluation_at' => $request->under_evaluation_at ?? now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'evaluator_changed', 'Evaluator changed', [
            'from' => $previous,
            'to' => $evaluatorUserId,
        ]);

        return $request->refresh();
    }
}
