<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Legacy import (Phase 7)
    |--------------------------------------------------------------------------
    |
    | Legacy connection is SELECT-only. Picture paths are checked with is_file()
    | only — image bytes are never loaded into memory.
    |
    */
    'legacy_connection' => env('LEGACY_DB_CONNECTION', 'legacy'),

    'chunk_size' => (int) env('LEGACY_IMPORT_CHUNK', 200),

    'picture_search_paths' => array_values(array_filter(array_map(
        static fn (string $path): string => rtrim(trim($path), '/'),
        explode(',', (string) env('LEGACY_PICTURE_PATHS', ''))
    ))),
];
