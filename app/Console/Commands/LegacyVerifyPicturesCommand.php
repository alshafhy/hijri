<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\PropertyPicture;
use App\Support\Legacy\PropertyPictureVerifier;
use Illuminate\Console\Command;

class LegacyVerifyPicturesCommand extends Command
{
    protected $signature = 'legacy:verify-pictures
                            {--path= : Override LEGACY_PICTURE_PATHS with a single base directory}
                            {--chunk=500 : Rows per chunk}
                            {--sample=15 : How many missing filenames to print}';

    protected $description = 'Re-check property_pictures against disk; updates only file_exists and relative_path';

    public function handle(PropertyPictureVerifier $verifier): int
    {
        $override = $this->option('path');
        $bases = is_string($override) && trim($override) !== ''
            ? [rtrim(trim($override), '/')]
            : config('legacy_import.picture_search_paths', []);

        if (! is_array($bases) || $bases === []) {
            $this->error('No picture base path configured.');
            $this->line('Set LEGACY_PICTURE_PATHS in .env, or pass --path=/absolute/path/to/images/requests');
            $this->line('Expected layout: {base}/{legacy_request_id}/{filename}');

            return self::FAILURE;
        }

        foreach ($bases as $base) {
            if (! is_dir($base)) {
                $this->warn("Base path is not a directory: {$base}");
            }
        }

        $chunk = max(1, (int) $this->option('chunk'));
        $sampleLimit = max(0, (int) $this->option('sample'));

        $total = 0;
        $found = 0;
        $missing = 0;
        $sample = [];

        PropertyPicture::query()
            ->with(['property.valuationRequest'])
            ->orderBy('id')
            ->chunkById($chunk, function ($pictures) use ($verifier, $bases, &$total, &$found, &$missing, &$sample, $sampleLimit): void {
                foreach ($pictures as $picture) {
                    $total++;
                    $result = $verifier->verifyAndPersist($picture, $bases);
                    if ($result['found']) {
                        $found++;
                    } else {
                        $missing++;
                        if (count($sample) < $sampleLimit) {
                            $sample[] = sprintf(
                                'id=%d legacy_id=%s request_legacy_id=%s filename=%s',
                                $picture->id,
                                (string) $picture->legacy_id,
                                $result['request_legacy_id'] ?? 'null',
                                $picture->filename
                            );
                        }
                    }
                }
            });

        $this->newLine();
        $this->info('=== Picture verification report ===');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Total rows', $total],
                ['Files found', $found],
                ['Files missing', $missing],
                ['Bases searched', implode(', ', $bases)],
            ]
        );

        if ($sample !== []) {
            $this->warn('Sample unresolved filenames:');
            foreach ($sample as $line) {
                $this->line('  '.$line);
            }
        }

        return self::SUCCESS;
    }
}
