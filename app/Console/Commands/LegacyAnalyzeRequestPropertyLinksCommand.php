<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Legacy\RequestPropertyLinker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class LegacyAnalyzeRequestPropertyLinksCommand extends Command
{
    protected $signature = 'legacy:analyze-request-property-links
                            {--connection=legacy : Legacy DB connection name}
                            {--write= : Optional path to write JSON report}';

    protected $description = 'Quantify request↔REI Location integrity on the legacy DB (read-only)';

    public function handle(RequestPropertyLinker $linker): int
    {
        $connection = (string) $this->option('connection');

        $this->info("Analyzing legacy connection [{$connection}] (read-only)...");

        $summary = $linker->summarize($connection);

        $this->table(
            ['Bucket', 'Count'],
            [
                ['certain (resolvable)', $summary['certain']],
                ['ambiguous', $summary['ambiguous']],
                ['orphan/unmatched', $summary['orphan']],
                ['total', $summary['certain'] + $summary['ambiguous'] + $summary['orphan']],
            ]
        );

        $this->info('By reason:');
        foreach ($summary['by_reason'] as $reason => $count) {
            $this->line("  {$reason}: {$count}");
        }

        $path = $this->option('write');
        if (is_string($path) && $path !== '') {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)."\n");
            $this->info("Wrote report to {$path}");
        }

        return self::SUCCESS;
    }
}
