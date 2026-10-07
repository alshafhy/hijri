<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use RuntimeException;

final class ChangePropertyTypeAction
{
    public function execute(
        User $actor,
        ValuationRequest $request,
        string $propertyType,
        ?string $propertyKind = null,
    ): ValuationRequest {
        Gate::forUser($actor)->authorize('changePropertyType', $request);

        $property = $request->property;
        if ($property === null) {
            throw new RuntimeException(__('Valuation has no linked property.'));
        }

        $previous = [
            'property_type' => $property->property_type,
            'property_kind' => $property->property_kind,
        ];

        DB::transaction(function () use ($property, $propertyType, $propertyKind): void {
            $payload = ['property_type' => $propertyType];
            if ($propertyKind !== null && $propertyKind !== '') {
                $payload['property_kind'] = $propertyKind;
            }
            $property->forceFill($payload)->save();
        });

        ValuationActivity::log($actor, $request, 'changed_property_type', 'Property type changed', [
            'previous' => $previous,
            'property_type' => $propertyType,
            'property_kind' => $propertyKind,
        ]);

        return $request->refresh()->load('property');
    }
}
