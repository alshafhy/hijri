<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\Valuation\ReportPdfCacheService;
use Illuminate\Console\Command;

final class PruneReportPdfCacheCommand extends Command
{
    protected $signature = 'report:prune-pdf-cache {--days=30 : Delete cached PDFs older than this many days}';

    protected $description = 'Delete stale cached valuation report PDFs';

    public function handle(ReportPdfCacheService $cache): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = $cache->prune($days);

        $this->info("Deleted {$deleted} cached file(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
