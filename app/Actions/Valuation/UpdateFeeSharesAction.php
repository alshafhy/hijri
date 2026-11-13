<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\RequestFeeShare;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class UpdateFeeSharesAction
{
    /**
     * @param  array{coordinator_share?: int, evaluator_share?: int, manager_share?: int}  $data
     */
    public function execute(User $actor, ValuationRequest $request, array $data): RequestFeeShare
    {
        Gate::forUser($actor)->authorize('manageFeeShares', $request);

        $share = $request->feeShares()->first();
        if ($share === null) {
            $share = new RequestFeeShare(['valuation_request_id' => $request->id]);
        }

        $share->fill([
            'coordinator_share' => (int) ($data['coordinator_share'] ?? $share->coordinator_share ?? 0),
            'evaluator_share' => (int) ($data['evaluator_share'] ?? $share->evaluator_share ?? 0),
            'manager_share' => (int) ($data['manager_share'] ?? $share->manager_share ?? 0),
            'valuation_request_id' => $request->id,
        ])->save();

        ValuationActivity::log($actor, $request, 'fee_shares_updated', 'Fee shares updated', [
            'shares' => $share->only(['coordinator_share', 'evaluator_share', 'manager_share']),
        ]);

        return $share->refresh();
    }
}
