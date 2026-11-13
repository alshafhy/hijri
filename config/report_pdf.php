<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('REPORT_PDF_CACHE_ENABLED', true),
    'queue' => (bool) env('REPORT_PDF_QUEUE', true),
    'queue_name' => env('REPORT_PDF_QUEUE_NAME', 'reports'),
    'lock_ttl' => (int) env('REPORT_PDF_LOCK_TTL', 1800),
    'paper' => env('REPORT_PDF_PAPER', 'a4'),
    'orientation' => env('REPORT_PDF_ORIENTATION', 'portrait'),
    'temp_dir' => storage_path('app/mpdf'),
    'font_dir' => resource_path('fonts'),
    'default_font' => 'dinnextlt',
    'fonts' => [
        'dinnextlt' => [
            'R' => 'dinnextlt/dinnextltarabic-light.ttf',
            'B' => 'dinnextlt/DINNextLTArabic-Medium.ttf',
            'useOTL' => 0xFF,
            'useKashida' => 75,
        ],
    ],
    'watermark_alpha' => 0.12,
];
