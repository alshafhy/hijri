<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class MarkUnderEvaluationAction
{
    public function execute(User $actor, ValuationRequest $request): ValuationRequest
    {
        Gate::forUser($actor)->authorize('markUnderEvaluation', $request);

        if ($request->evaluator_user_id === null) {
            $request->forceFill(['evaluator_user_id' => $actor->id]);
        }

        $request->forceFill([
            'state' => 'underEvaluative',
            'under_evaluation_at' => now(),
        ])->save();

        ValuationActivity::log($actor, $request, 'under_evaluation', 'Valuation marked under evaluation');

        return $request->refresh();
    }
}
