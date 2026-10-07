<?php

declare(strict_types=1);

namespace App\Actions\Partner;

use App\Models\Partner;

final class UpdatePartnerAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(Partner $partner, array $data): Partner
    {
        $partner->fill([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'x_axis' => $data['x_axis'] ?? null,
            'y_axis' => $data['y_axis'] ?? null,
        ])->save();

        return $partner->refresh();
    }
}
