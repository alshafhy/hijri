<?php

declare(strict_types=1);

namespace App\Support\Valuation\Report;

final class ReportLabels
{
    private const array PLACEHOLDERS = ['اختر'];

    public function option(string $list, mixed $code): ?string
    {
        if ($code === null || ! is_scalar($code) || trim((string) $code) === '') {
            return null;
        }

        $key = trim((string) $code);
        $options = $this->options($list);

        if ($options === null || ! array_key_exists($key, $options) || ! is_string($options[$key])) {
            return $key;
        }

        $label = trim($options[$key]);

        return $this->isPlaceholder($label) ? null : $this->translate($label);
    }

    public function csv(?string $csv, string $glue = '، '): ?string
    {
        $tokens = array_filter(
            array_map('trim', explode(',', (string) $csv)),
            fn (string $token): bool => $token !== '',
        );

        return $tokens === [] ? null : implode($glue, array_map($this->translate(...), $tokens));
    }

    /**
     * @return array<int|string, mixed>|null
     */
    private function options(string $list): ?array
    {
        $config = config("constants.select.{$list}");

        if (! is_array($config)) {
            return null;
        }

        return is_array($config['options'] ?? null) ? $config['options'] : $config;
    }

    private function isPlaceholder(string $label): bool
    {
        return $label === '' || str_starts_with($label, '--') || in_array($label, self::PLACEHOLDERS, true);
    }

    private function translate(string $label): string
    {
        $translated = __($label);

        return is_string($translated) ? $translated : $label;
    }
}
