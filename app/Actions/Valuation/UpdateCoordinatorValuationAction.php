<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Actions\Valuation\Concerns\WritesInitialData;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use App\Support\Valuation\ValuationUploadStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

/**
 * Edit initial data (legacy `edit_min`): blocked once approved or uploaded to Qima.
 */
final class UpdateCoordinatorValuationAction
{
    use WritesInitialData;

    public function __construct(private readonly ValuationUploadStore $uploads) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, ValuationRequest $request, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('editInitialData', $request);

        try {
            return DB::transaction(function () use ($actor, $request, $data): ValuationRequest {
                $request->fill([
                    'number' => $data['number'] ?? $request->number,
                    'deposit_number' => array_key_exists('deposit_number', $data) ? $data['deposit_number'] : $request->deposit_number,
                    'evaluator_user_id' => $data['evaluator_user_id'] ?? $request->evaluator_user_id,
                    'company_id' => array_key_exists('company_id', $data) ? $data['company_id'] : $request->company_id,
                ])->save();

                $property = $request->property()->firstOrCreate(['valuation_request_id' => $request->id]);
                $property->fill($this->initialPropertyAttributes($data))->save();
                $this->writeLocation($property, $data);
                $this->writeBorders($property, (array) ($data['borders'] ?? []));

                $this->uploads->storeAttachments($actor, $request, (array) ($data['new_attachments'] ?? []));

                ValuationActivity::log($actor, $request, 'coordinator_updated', 'Initial data updated');

                return $request->fresh(['property.location', 'feeShares']) ?? $request;
            });
        } catch (Throwable $e) {
            $this->uploads->rollback();
            throw $e;
        }
    }
}
