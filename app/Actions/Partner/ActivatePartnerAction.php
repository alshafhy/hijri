<?php

declare(strict_types=1);

namespace App\Actions\Partner;

use App\Models\Partner;

final class ActivatePartnerAction
{
    public function __invoke(Partner $partner): Partner
    {
        $partner->forceFill(['state' => Partner::STATE_ACTIVE])->save();

        activity('Partner')
            ->performedOn($partner)
            ->withProperties(['action' => 'activated', 'state' => Partner::STATE_ACTIVE])
            ->log('Partner activated');

        return $partner->refresh();
    }
}
