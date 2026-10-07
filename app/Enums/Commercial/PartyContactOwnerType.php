<?php

declare(strict_types=1);

namespace App\Enums\Commercial;

enum PartyContactOwnerType: string
{
    case Partner = 'partner';
    case Contractor = 'contractor';
}
