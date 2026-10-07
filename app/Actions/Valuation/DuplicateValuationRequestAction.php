<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\Property;
use App\Models\PropertyComponent;
use App\Models\PropertyLocation;
use App\Models\PropertyTotal;
use App\Models\RequestFeeShare;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class DuplicateValuationRequestAction
{
    public function execute(User $actor, ValuationRequest $source): ValuationRequest
    {
        Gate::forUser($actor)->authorize('duplicate', $source);

        return DB::transaction(function () use ($actor, $source): ValuationRequest {
            $source->loadMissing([
                'property.location',
                'property.components',
                'property.total',
                'feeShares',
            ]);

            $duplicate = $source->replicate([
                'legacy_id',
                'official_report_path',
                'qima_locked_at',
                'uploaded_on_qima',
                'ended_at',
                'evaluated_at',
                'under_evaluation_at',
                'approve',
            ]);
            $duplicate->legacy_id = null;
            $duplicate->state = 'waiting';
            $duplicate->approve = 0;
            $duplicate->uploaded_on_qima = false;
            $duplicate->official_report_path = null;
            $duplicate->qima_locked_at = null;
            $duplicate->started_at = now();
            $duplicate->under_evaluation_at = null;
            $duplicate->evaluated_at = null;
            $duplicate->ended_at = null;
            $duplicate->coordinator_user_id = $actor->id;
            $duplicate->save();

            $duplicate->forceFill([
                'reference' => (int) $duplicate->id + 10000,
                'number' => (string) ((int) $duplicate->id + 10000),
            ])->save();

            if ($source->property instanceof Property) {
                $newProperty = $source->property->replicate(['legacy_id', 'valuation_request_id']);
                $newProperty->legacy_id = null;
                $newProperty->valuation_request_id = $duplicate->id;
                $newProperty->save();

                if ($source->property->location instanceof PropertyLocation) {
                    $loc = $source->property->location->replicate(['legacy_location_id', 'property_id']);
                    $loc->legacy_location_id = null;
                    $loc->property_id = $newProperty->id;
                    $loc->save();
                }

                foreach ($source->property->components as $component) {
                    /** @var PropertyComponent $component */
                    $copy = $component->replicate(['property_id']);
                    $copy->property_id = $newProperty->id;
                    $copy->save();
                }

                if ($source->property->total instanceof PropertyTotal) {
                    $total = $source->property->total->replicate(['legacy_id', 'property_id']);
                    $total->legacy_id = null;
                    $total->property_id = $newProperty->id;
                    $total->save();
                }
            }

            $fee = $source->feeShares->first();
            if ($fee instanceof RequestFeeShare) {
                $feeCopy = $fee->replicate(['legacy_id', 'valuation_request_id']);
                $feeCopy->legacy_id = null;
                $feeCopy->valuation_request_id = $duplicate->id;
                $feeCopy->save();
            } else {
                RequestFeeShare::query()->create([
                    'valuation_request_id' => $duplicate->id,
                    'coordinator_share' => 0,
                    'evaluator_share' => 0,
                    'manager_share' => 0,
                ]);
            }

            ValuationActivity::log($actor, $duplicate, 'duplicated', 'Valuation duplicated', [
                'source_id' => $source->id,
            ]);

            return $duplicate->fresh(['property', 'feeShares']) ?? $duplicate;
        });
    }
}
