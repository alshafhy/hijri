<?php

declare(strict_types=1);

namespace App\Support\Legacy;

final class YesNoNormalizer
{
    /**
     * @return bool|null null when value is empty/unknown (do not guess)
     */
    public static function toBool(mixed $value): ?bool
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
        $normalized = str_replace(['ـ', ' '], '', $normalized);

        return match ($normalized) {
            'نعم', 'yes', 'y', 'true', '1' => true,
            'لا', 'no', 'n', 'false', '0' => false,
            '' => null,
            default => null,
        };
    }
}
