<?php

declare(strict_types=1);

return [
    'enabled' => (bool) env('REPORT_PDF_CACHE_ENABLED', true),
    'queue' => (bool) env('REPORT_PDF_QUEUE', true),
    'queue_name' => env('REPORT_PDF_QUEUE_NAME', 'reports'),
    'lock_ttl' => (int) env('REPORT_PDF_LOCK_TTL', 600),
    'paper' => env('REPORT_PDF_PAPER', 'a4'),
    'orientation' => env('REPORT_PDF_ORIENTATION', 'portrait'),
];
