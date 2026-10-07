<?php

declare(strict_types=1);

namespace App\Enums\Commercial;

enum PartyContactStatus: int
{
    case Inactive = -1;
    case Pending = 1;
    case Active = 2;

    public function label(): string
    {
        return match ($this) {
            self::Inactive => __('Inactive'),
            self::Pending => __('Pending'),
            self::Active => __('Active'),
        };
    }
}
