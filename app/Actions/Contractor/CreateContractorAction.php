<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Models\Contractor;

final class CreateContractorAction
{
    /** @param array<string, mixed> $data */
    public function __invoke(array $data): Contractor
    {
        $contractor = Contractor::query()->create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'fees' => 0,
            'template_id' => 0,
            'state' => Contractor::STATE_DRAFT,
        ]);

        activity('Contractor')
            ->performedOn($contractor)
            ->withProperties(['action' => 'created'])
            ->log('Contractor created');

        return $contractor;
    }
}
