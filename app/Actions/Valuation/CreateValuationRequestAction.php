<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\Property;
use App\Models\PropertyLocation;
use App\Models\RequestFeeShare;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class CreateValuationRequestAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('create', ValuationRequest::class);

        return DB::transaction(function () use ($actor, $data): ValuationRequest {
            $request = ValuationRequest::query()->create([
                'number' => $data['number'] ?? null,
                'deposit_number' => $data['deposit_number'] ?? null,
                'company_id' => $data['company_id'] ?? null,
                'coordinator_user_id' => $data['coordinator_user_id'] ?? $actor->id,
                'evaluator_user_id' => $data['evaluator_user_id'] ?? null,
                'sub_user_id' => $data['sub_user_id'] ?? null,
                'fellow_user_id' => $data['fellow_user_id'] ?? null,
                'state' => 'waiting',
                'approve' => 0,
                'started_at' => now(),
                'uploaded_on_qima' => false,
            ]);

            $request->forceFill([
                'reference' => (int) $request->id + 10000,
            ])->save();

            if (empty($data['number'])) {
                $request->forceFill(['number' => (string) $request->reference])->save();
            }

            $property = Property::query()->create([
                'valuation_request_id' => $request->id,
                'customer_name' => $data['customer_name'] ?? null,
                'owner_name' => $data['owner_name'] ?? null,
                'property_kind' => $data['property_kind'] ?? null,
                'property_type' => $data['property_type'] ?? null,
                'instrument_no' => $data['instrument_no'] ?? null,
                'instrument_date' => $data['instrument_date'] ?? null,
                'instrument_gregorian_date' => $data['instrument_gregorian_date'] ?? null,
                'deposit_number' => $data['deposit_number'] ?? null,
            ]);

            if (! empty($data['city_id']) || ! empty($data['street']) || ! empty($data['x_axis'])) {
                PropertyLocation::query()->create([
                    'property_id' => $property->id,
                    'city_id' => $data['city_id'] ?? null,
                    'neighborhood_id' => $data['neighborhood_id'] ?? null,
                    'street' => $data['street'] ?? null,
                    'sketch_no' => $data['sketch_no'] ?? null,
                    'part_no' => $data['part_no'] ?? null,
                    'piece_number' => $data['piece_number'] ?? null,
                    'x_axis' => $data['x_axis'] ?? null,
                    'y_axis' => $data['y_axis'] ?? null,
                ]);
            }

            RequestFeeShare::query()->create([
                'valuation_request_id' => $request->id,
                'coordinator_share' => (int) ($data['coordinator_share'] ?? 0),
                'evaluator_share' => (int) ($data['evaluator_share'] ?? 0),
                'manager_share' => (int) ($data['manager_share'] ?? 0),
            ]);

            ValuationActivity::log($actor, $request, 'created', 'Valuation request created');

            return $request->fresh(['property.location', 'feeShares']) ?? $request;
        });
    }
}
