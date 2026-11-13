<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\PropertyTotal;
use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class OverrideTotalAmountManualAction
{
    public function execute(User $actor, ValuationRequest $request, ?float $amount): PropertyTotal
    {
        Gate::forUser($actor)->authorize('overrideAmount', $request);

        $property = $request->property;
        abort_if($property === null, 404);

        $total = PropertyTotal::query()->firstOrNew(['property_id' => $property->id]);
        $previous = $total->total_amount_manual;
        $total->total_amount_manual = $amount;
        $total->save();

        ValuationActivity::log($actor, $request, 'amount_overridden', 'Manual total amount overridden', [
            'from' => $previous,
            'to' => $amount,
        ]);

        return $total->refresh();
    }
}
