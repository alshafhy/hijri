<?php

declare(strict_types=1);

namespace App\Actions\Contractor;

use App\Models\Contractor;
use Illuminate\Support\Facades\DB;

final class DeactivateContractorAction
{
    public function __invoke(Contractor $contractor): void
    {
        DB::transaction(function () use ($contractor): void {
            $contractor->forceFill(['state' => Contractor::STATE_INACTIVE])->save();
            $contractor->delete();

            activity('Contractor')
                ->performedOn($contractor)
                ->withProperties(['action' => 'deactivated'])
                ->log('Contractor deactivated');
        });
    }
}
