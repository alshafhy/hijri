<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Actions\Valuation\Concerns\WritesInitialData;
use App\Models\Company;
use App\Models\Contract;
use App\Models\PartnerContract;
use App\Models\Property;
use App\Models\PropertyComponent;
use App\Models\RequestFeeShare;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use App\Support\Valuation\ValuationFieldGrid;
use App\Support\Valuation\ValuationUploadStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Throwable;

final class CreateValuationRequestAction
{
    use WritesInitialData;

    public const CLIENT_CONTRACTOR = 1;

    public const CLIENT_PARTNER = 2;

    public function __construct(private readonly ValuationUploadStore $uploads) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('create', ValuationRequest::class);

        try {
            return DB::transaction(fn (): ValuationRequest => $this->create($actor, $data));
        } catch (Throwable $e) {
            $this->uploads->rollback();
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function create(User $actor, array $data): ValuationRequest
    {
        $company = isset($data['company_id'])
            ? Company::query()->find($data['company_id'])
            : Company::query()->orderBy('id')->first();

        $request = ValuationRequest::query()->create([
            'number' => $data['number'] ?? null,
            'deposit_number' => $data['deposit_number'] ?? null,
            'company_id' => $company?->id,
            'coordinator_user_id' => $data['coordinator_user_id'] ?? $actor->id,
            'evaluator_user_id' => $data['evaluator_user_id'] ?? null,
            'sub_user_id' => $data['sub_user_id'] ?? null,
            'state' => 'waiting',
            'approve' => 0,
            'started_at' => now(),
            'uploaded_on_qima' => false,
        ]);

        $request->forceFill(['reference' => (int) $request->id + 10000])->save();
        if (empty($data['number'])) {
            $request->forceFill(['number' => (string) $request->reference])->save();
        }

        $this->linkClient($request, $data);

        $property = Property::query()->create(array_merge($this->initialPropertyAttributes($data), [
            'valuation_request_id' => $request->id,
            'property_kind' => $data['property_kind'] ?? null,
            'property_type' => $data['property_type'] ?? null,
            'deposit_number' => $data['deposit_number'] ?? null,
            'print_valuation_certificate' => 1,
        ]));

        if (! empty($data['city_id']) || ! empty($data['x_axis'])) {
            $this->writeLocation($property, $data);
        }
        $this->writeBorders($property, (array) ($data['borders'] ?? []));
        $this->writeComponentAreas($property, (array) ($data['components'] ?? []));

        RequestFeeShare::query()->create([
            'valuation_request_id' => $request->id,
            'coordinator_share' => (int) ($data['coordinator_share'] ?? $company->default_coordinator_share ?? 0),
            'evaluator_share' => (int) ($data['evaluator_share'] ?? $company->default_evaluator_share ?? 0),
            'manager_share' => (int) ($data['manager_share'] ?? $company->default_manager_share ?? 0),
        ]);

        $this->uploads->storeAttachments($actor, $request, (array) ($data['new_attachments'] ?? []));

        ValuationActivity::log($actor, $request, 'created', 'Valuation request created');

        return $request->fresh(['property.location', 'feeShares']) ?? $request;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function linkClient(ValuationRequest $request, array $data): void
    {
        $clientType = (int) ($data['client_type'] ?? 0);

        if ($clientType === self::CLIENT_CONTRACTOR && ! empty($data['contractor_id'])) {
            Contract::query()->create([
                'contractor_id' => $data['contractor_id'],
                'valuation_request_id' => $request->id,
                'state' => Contract::STATE_UNPAID,
            ]);
        }

        if ($clientType === self::CLIENT_PARTNER && ! empty($data['partner_id'])) {
            PartnerContract::query()->create([
                'partner_id' => $data['partner_id'],
                'offer_estate_id' => $data['offer_estate_id'] ?? null,
                'valuation_request_id' => $request->id,
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $components
     */
    private function writeComponentAreas(Property $property, array $components): void
    {
        $allowed = array_column(ValuationFieldGrid::rows((string) $property->property_kind, (string) $property->property_type), 'key');

        foreach ($components as $key => $values) {
            $area = $values['area_value'] ?? null;
            if (! in_array($key, $allowed, true) || $area === null || $area === '') {
                continue;
            }
            PropertyComponent::query()->create([
                'property_id' => $property->id,
                'component_key' => $key,
                'area_value' => $area,
            ]);
        }
    }
}
