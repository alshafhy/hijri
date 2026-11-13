<?php

declare(strict_types=1);

namespace App\Enums\Commercial;

enum OfferEstatePaymentStatus: int
{
    case Unpaid = 0;
    case Paid = 1;

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => __('Unpaid'),
            self::Paid => __('Paid'),
        };
    }
}
