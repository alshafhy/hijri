<?php

declare(strict_types=1);

/**
 * @return array<string, mixed>
 */
function langArray(string $locale, string $relative): array
{
    $path = resource_path("lang/{$locale}/{$relative}");
    expect(is_file($path))->toBeTrue("Missing {$locale}/{$relative}");

    /** @var array<string, mixed> $data */
    $data = require $path;

    return $data;
}

/**
 * @param  array<string, mixed>  $data
 * @return list<string>
 */
function flattenLangKeys(array $data, string $prefix = ''): array
{
    $keys = [];
    foreach ($data as $key => $value) {
        $full = $prefix === '' ? (string) $key : $prefix.'.'.$key;
        if (is_array($value)) {
            $keys = array_merge($keys, flattenLangKeys($value, $full));
        } else {
            $keys[] = $full;
        }
    }

    return $keys;
}

it('resolves user and crud labels in arabic and english', function () {
    foreach (['ar', 'en'] as $locale) {
        expect(__('crud.edit', [], $locale))->not->toBe('crud.edit')
            ->and(__('crud.save', [], $locale))->not->toBe('crud.save')
            ->and(__('crud.cancel', [], $locale))->not->toBe('crud.cancel')
            ->and(__('models/users.fields.branch_name', [], $locale))->not->toBe('models/users.fields.branch_name')
            ->and(__('models/users.fields.name', [], $locale))->not->toBe('models/users.fields.name');
    }

    expect(__('crud.edit', [], 'ar'))->toBe('تعديل')
        ->and(__('crud.edit', [], 'en'))->toBe('Edit')
        ->and(__('models/users.fields.branch_name', [], 'en'))->toBe('Branch')
        ->and(__('models/roles.fields.permissions', [], 'ar'))->toBe('الصلاحيات');
});

it('labels deactivate as تعطيل in arabic', function () {
    expect(__('Deactivate', [], 'ar'))->toBe('تعطيل')
        ->and(__('Deactivate', [], 'en'))->toBe('Deactivate')
        ->and(__('Cancel', [], 'ar'))->toBe('إلغاء');
});

it('keeps every arabic php translation key in english files', function () {
    $base = resource_path('lang/ar');
    $missing = [];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $relative = substr($file->getPathname(), strlen($base) + 1);
        if (str_starts_with($relative, 'vendor/')) {
            continue;
        }

        $enPath = resource_path('lang/en/'.$relative);
        if (! is_file($enPath)) {
            $missing[] = "missing file en/{$relative}";

            continue;
        }

        $arKeys = flattenLangKeys(langArray('ar', $relative));
        $enKeys = flattenLangKeys(langArray('en', $relative));
        foreach ($arKeys as $key) {
            if (! in_array($key, $enKeys, true)) {
                $missing[] = str_replace('.php', '', "en/{$relative}").'.'.$key;
            }
        }
    }

    expect($missing)->toBeEmpty(implode("\n", array_slice($missing, 0, 40)));
});
