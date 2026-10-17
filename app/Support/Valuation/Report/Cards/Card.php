<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report\Cards;

/**
 * Report card (title + label/value rows) with the value formatting shared by the card builders.
 */
final class Card
{
    /**
     * @param  list<array{0: string, 1: ?string}>  $rows
     * @return array{title: string, rows: list<array{label: string, value: string}>}|null
     */
    public static function make(string $title, array $rows): ?array
    {
        $kept = [];
        foreach ($rows as [$label, $value]) {
            if ($value !== null && trim($value) !== '') {
                $kept[] = ['label' => $label, 'value' => trim($value)];
            }
        }

        return $kept === [] ? null : ['title' => $title, 'rows' => $kept];
    }

    public static function label(string $key): string
    {
        $text = __("pdf.{$key}");

        return is_string($text) ? $text : $key;
    }

    /**
     * Legacy `safeTrans`: a stored value that is itself a pdf.php key prints translated, anything else verbatim.
     */
    public static function text(mixed $value): ?string
    {
        if (! is_scalar($value)) {
            return null;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        $translated = __("pdf.{$text}");

        return is_string($translated) && $translated !== "pdf.{$text}" ? $translated : $text;
    }

    public static function number(mixed $value, ?string $unit = null): ?string
    {
        if (! is_numeric($value)) {
            return self::text($value);
        }

        $formatted = rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');

        return $unit === null ? $formatted : "{$formatted} {$unit}";
    }

    public static function positive(mixed $value, ?string $unit = null): ?string
    {
        return is_numeric($value) && (float) $value > 0 ? self::number($value, $unit) : null;
    }

    /**
     * @param  list<?string>  $parts
     */
    public static function join(array $parts, string $glue): ?string
    {
        $parts = array_values(array_filter($parts, static fn (?string $part): bool => $part !== null && trim($part) !== ''));

        return $parts === [] ? null : implode($glue, $parts);
    }
}
