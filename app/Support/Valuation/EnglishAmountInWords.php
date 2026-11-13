<?php

declare(strict_types=1);

namespace App\Support\Valuation;

final class EnglishAmountInWords
{
    private const ONES = [
        '', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten',
        'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen',
    ];

    private const TENS = ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'];

    private const SCALES = ['', 'thousand', 'million', 'billion'];

    public function convert(float|int|null $number): string
    {
        if ($number === null) {
            return '';
        }

        $integer = (int) round((float) $number);

        if ($integer < 0 || $integer > 999_999_999_999) {
            return '';
        }

        if ($integer === 0) {
            return 'Zero';
        }

        $words = [];
        foreach (array_reverse(str_split(strrev((string) $integer), 3), true) as $scale => $chunk) {
            $value = (int) strrev($chunk);
            if ($value > 0) {
                $words[] = trim($this->hundreds($value).' '.self::SCALES[$scale]);
            }
        }

        return ucfirst(implode(' ', $words));
    }

    private function hundreds(int $value): string
    {
        $parts = [];

        if ($value >= 100) {
            $parts[] = self::ONES[intdiv($value, 100)].' hundred';
            $value %= 100;
        }

        if ($value >= 20) {
            $parts[] = self::TENS[intdiv($value, 10)].($value % 10 > 0 ? '-'.self::ONES[$value % 10] : '');
        } elseif ($value > 0) {
            $parts[] = self::ONES[$value];
        }

        return implode(' ', $parts);
    }
}
