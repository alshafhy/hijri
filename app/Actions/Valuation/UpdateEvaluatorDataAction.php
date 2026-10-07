<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\PropertyComponent;
use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

final class UpdateEvaluatorDataAction
{
    public function __construct(
        private readonly ComputeComponentValuationTotalAction $computeTotal,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(User $actor, ValuationRequest $request, array $data): ValuationRequest
    {
        Gate::forUser($actor)->authorize('update', $request);

        return DB::transaction(function () use ($actor, $request, $data): ValuationRequest {
            $property = $request->property;
            abort_if($property === null, 404);

            $property->fill([
                'customer_name' => $data['customer_name'] ?? $property->customer_name,
                'owner_name' => $data['owner_name'] ?? $property->owner_name,
                'customer_name_en' => $data['customer_name_en'] ?? $property->customer_name_en,
                'owner_name_en' => $data['owner_name_en'] ?? $property->owner_name_en,
                'property_kind' => $data['property_kind'] ?? $property->property_kind,
                'property_type' => $data['property_type'] ?? $property->property_type,
                'instrument_no' => $data['instrument_no'] ?? $property->instrument_no,
                'instrument_date' => $data['instrument_date'] ?? $property->instrument_date,
                'instrument_gregorian_date' => $data['instrument_gregorian_date'] ?? $property->instrument_gregorian_date,
                'notes' => $data['notes'] ?? $property->notes,
                'notes_property' => $data['notes_property'] ?? $property->notes_property,
                'notes_real_estate' => $data['notes_real_estate'] ?? $property->notes_real_estate,
                'valuation_way_type' => $data['valuation_way_type'] ?? $property->valuation_way_type,
                'base_value' => $data['base_value'] ?? $property->base_value,
                'forced_sale_percentage' => $data['forced_sale_percentage'] ?? $property->forced_sale_percentage,
                'evaluation_date' => $data['evaluation_date'] ?? $property->evaluation_date,
            ])->save();

            if (isset($data['location']) && is_array($data['location'])) {
                $locationData = array_intersect_key($data['location'], array_flip([
                    'city_id', 'neighborhood_id', 'street', 'sketch_no', 'part_no',
                    'piece_number', 'x_axis', 'y_axis', 'sketch_name',
                ]));
                $property->location()->updateOrCreate(
                    ['property_id' => $property->id],
                    $locationData
                );
            }

            /** @var array<string, array{area_value?: mixed, price_value?: mixed}> $components */
            $components = $data['components'] ?? [];
            foreach ($components as $key => $values) {
                if (! is_string($key) || $key === '') {
                    continue;
                }
                PropertyComponent::query()->updateOrCreate(
                    ['property_id' => $property->id, 'component_key' => $key],
                    [
                        'area_value' => $values['area_value'] ?? null,
                        'price_value' => $values['price_value'] ?? null,
                    ]
                );
            }

            $calculated = $this->computeTotal->execute($property->fresh(['components']) ?? $property);
            PropertyTotal::query()->updateOrCreate(
                ['property_id' => $property->id],
                [
                    'total_amount' => $calculated,
                    'forced_sale_percentage' => $property->forced_sale_percentage,
                ]
            );

            ValuationActivity::log($actor, $request, 'evaluator_data_updated', 'Evaluator dossier data updated');

            return $request->fresh(['property.location', 'property.components', 'property.total']) ?? $request;
        });
    }
}
