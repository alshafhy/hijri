<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Arabic/English yes-no tokens used across legacy valuation fields.
 */
enum YesNo: string
{
    case Yes = 'yes';
    case No = 'no';

    public function toBoolean(): bool
    {
        return $this === self::Yes;
    }

    public function label(): string
    {
        return match ($this) {
            self::Yes => __('enums.yes_no.yes'),
            self::No => __('enums.yes_no.no'),
        };
    }

    /**
     * @return list<string>
     */
    public static function yesTokens(): array
    {
        return ['yes', 'y', '1', 'true', 'نعم', 'ايوه', 'أيوه'];
    }

    /**
     * @return list<string>
     */
    public static function noTokens(): array
    {
        return ['no', 'n', '0', 'false', 'لا'];
    }
}
