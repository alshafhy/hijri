<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\YesNo;

/**
 * Normalize legacy نعم/لا (and related tokens) into nullable booleans.
 */
final class BooleanNormalizer
{
    public static function fromMixed(mixed $value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            if ((int) $value === 1) {
                return true;
            }
            if ((int) $value === 0) {
                return false;
            }

            return null;
        }

        $normalized = mb_strtolower(trim((string) $value));
        if ($normalized === '') {
            return null;
        }

        if (in_array($normalized, YesNo::yesTokens(), true)) {
            return true;
        }

        if (in_array($normalized, YesNo::noTokens(), true)) {
            return false;
        }

        return null;
    }

    public static function toYesNo(mixed $value): ?YesNo
    {
        $bool = self::fromMixed($value);

        return match ($bool) {
            true => YesNo::Yes,
            false => YesNo::No,
            null => null,
        };
    }
}
