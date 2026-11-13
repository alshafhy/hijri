<?php

declare(strict_types=1);

namespace App\Actions\Valuation;

use App\Models\User;
use App\Models\ValuationRequest;
use App\Support\Valuation\ValuationActivity;
use Illuminate\Support\Facades\Gate;

final class ToggleQimaUploadedStatusAction
{
    public function execute(User $actor, ValuationRequest $request, bool $uploaded): ValuationRequest
    {
        Gate::forUser($actor)->authorize('toggleQimaStatus', $request);

        $previous = (bool) $request->uploaded_on_qima;

        $request->forceFill([
            'uploaded_on_qima' => $uploaded,
            'qima_locked_at' => $uploaded ? ($request->qima_locked_at ?? now()) : null,
        ])->save();

        ValuationActivity::log($actor, $request, 'toggled_qima_status', 'Qima upload status toggled', [
            'previous' => $previous,
            'uploaded_on_qima' => $uploaded,
        ]);

        return $request->refresh();
    }
}
