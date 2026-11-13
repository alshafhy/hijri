<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Models\Contractor;
use Carbon\Carbon;

final class UpdateContractorAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Contractor $contractor, array $data): Contractor
    {
        $dateFrom = $data['date_from'] ?? null;
        $dateTo = $data['date_to'] ?? null;

        if ($dateFrom && ! $dateTo) {
            $dateTo = Carbon::parse($dateFrom)->addDays(365)->toDateString();
        }

        $contractor->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'x_axis' => $data['x_axis'] ?? null,
            'y_axis' => $data['y_axis'] ?? null,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'fees' => $data['fees'] ?? $contractor->fees,
            'template_id' => $data['template_id'] ?? $contractor->template_id,
        ])->save();

        if ($contractor->state === Contractor::STATE_DRAFT && $dateFrom) {
            $contractor->forceFill(['state' => Contractor::STATE_ACTIVE])->save();
        }

        return $contractor->refresh();
    }
}
