<?php

declare(strict_types=1);

namespace App\Enums;

enum UserStatus: int
{
    case Inactive = 0;
    case Active = 1;

    public function label(): string
    {
        return match ($this) {
            self::Inactive => __('enums.user_status.inactive'),
            self::Active => __('enums.user_status.active'),
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }
}
