<?php

declare(strict_types=1);

namespace App\Actions\Partner;

use App\Models\Partner;

final class CreatePartnerAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): Partner
    {
        $partner = Partner::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'x_axis' => $data['x_axis'] ?? null,
            'y_axis' => $data['y_axis'] ?? null,
            'state' => Partner::STATE_DRAFT,
        ]);

        activity('Partner')
            ->performedOn($partner)
            ->withProperties(['action' => 'created'])
            ->log('Partner created');

        return $partner;
    }
}
