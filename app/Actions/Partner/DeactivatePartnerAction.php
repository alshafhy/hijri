<?php

declare(strict_types=1);

namespace App\Actions\Partner;

use App\Models\Partner;
use Illuminate\Support\Facades\DB;

final class DeactivatePartnerAction
{
    public function __invoke(Partner $partner): void
    {
        DB::transaction(function () use ($partner): void {
            $partner->forceFill(['state' => Partner::STATE_INACTIVE])->save();
            $partner->delete();

            activity('Partner')
                ->performedOn($partner)
                ->withProperties(['action' => 'deactivated'])
                ->log('Partner deactivated');
        });
    }
}
