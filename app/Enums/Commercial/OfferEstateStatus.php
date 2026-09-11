<?php

declare(strict_types=1);

namespace App\Enums\Commercial;

enum OfferEstateStatus: int
{
    case Draft = 0;
    case Inactive = 1;
    case Active = 2;

    public function label(): string
    {
        return match ($this) {
            self::Draft => __('Draft'),
            self::Inactive => __('Inactive'),
            self::Active => __('Active'),
        };
    }
}
