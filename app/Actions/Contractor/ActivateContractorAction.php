<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Models\Contractor;

final class ActivateContractorAction
{
    public function __invoke(Contractor $contractor): Contractor
    {
        $contractor->forceFill(['state' => Contractor::STATE_ACTIVE])->save();

        activity('Contractor')
            ->performedOn($contractor)
            ->withProperties(['action' => 'activated'])
            ->log('Contractor activated');

        return $contractor->refresh();
    }
}
