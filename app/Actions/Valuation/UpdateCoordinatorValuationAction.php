<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UpdateCoordinatorValuationAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, ValuationRequest $request, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('update', $request);

        return DB::transaction(function () use ($actor, $request, $data): ValuationRequest {
            $request->fill([
                'number' => $data['number'] ?? $request->number,
                'deposit_number' => $data['deposit_number'] ?? $request->deposit_number,
                'evaluator_user_id' => array_key_exists('evaluator_user_id', $data)
                    ? $data['evaluator_user_id']
                    : $request->evaluator_user_id,
                'company_id' => array_key_exists('company_id', $data)
                    ? $data['company_id']
                    : $request->company_id,
            ])->save();

            $property = $request->property;
            if ($property !== null) {
                $property->fill([
                    'customer_name' => $data['customer_name'] ?? $property->customer_name,
                    'owner_name' => $data['owner_name'] ?? $property->owner_name,
                    'instrument_no' => $data['instrument_no'] ?? $property->instrument_no,
                    'property_kind' => $data['property_kind'] ?? $property->property_kind,
                    'property_type' => $data['property_type'] ?? $property->property_type,
                ])->save();
            }

            ValuationActivity::log($actor, $request, 'coordinator_updated', 'Coordinator fields updated');

            return $request->fresh(['property', 'feeShares']) ?? $request;
        });
    }
}
