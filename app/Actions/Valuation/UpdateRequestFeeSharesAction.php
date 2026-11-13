<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\RequestFeeShare;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UpdateRequestFeeSharesAction
{
    /**
     * @param  array{coordinator_share?:int|null,evaluator_share?:int|null,manager_share?:int|null}  $shares
     */
    public function execute(User $actor, ValuationRequest $request, array $shares): RequestFeeShare
    {
        Gate::forUser($actor)->authorize('manageFeeShares', $request);

        $feeShare = DB::transaction(function () use ($request, $shares): RequestFeeShare {
            $existingLegacyId = RequestFeeShare::query()
                ->where('valuation_request_id', $request->id)
                ->value('legacy_id');

            /** @var RequestFeeShare $feeShare */
            $feeShare = RequestFeeShare::query()->updateOrCreate(
                ['valuation_request_id' => $request->id],
                [
                    'legacy_id' => $existingLegacyId ?? (800000000 + $request->id),
                    'coordinator_share' => $shares['coordinator_share'] ?? null,
                    'evaluator_share' => $shares['evaluator_share'] ?? null,
                    'manager_share' => $shares['manager_share'] ?? null,
                ]
            );

            return $feeShare;
        });

        ValuationActivity::log($actor, $request, 'updated_fee_shares', 'Request fee shares updated', [
            'shares' => $shares,
        ]);

        return $feeShare->refresh();
    }
}
