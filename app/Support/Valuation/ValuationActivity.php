<?php

declare(strict_types=1);

namespace App\Support\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;

final class ValuationActivity
{
    public static function log(
        User $actor,
        ValuationRequest $request,
        string $event,
        string $description,
        array $properties = []
    ): void {
        activity('valuation')
            ->performedOn($request)
            ->causedBy($actor)
            ->withProperties(array_merge([
                'event' => $event,
                'valuation_request_id' => $request->id,
                'reference' => $request->reference,
                'number' => $request->number,
            ], $properties))
            ->event($event)
            ->log($description);
    }
}
