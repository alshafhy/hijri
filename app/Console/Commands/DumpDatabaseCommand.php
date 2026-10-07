<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Database\DumpDatabaseAction;
use Illuminate\Console\Command;
use RuntimeException;

final class DumpDatabaseCommand extends Command
{
    protected $signature = 'db:dump {--connection= : Database connection to back up (defaults to the default connection)}';

    protected $description = 'Write a gzipped mysqldump backup to storage/app/private/backups';

    public function handle(DumpDatabaseAction $dump): int
    {
        try {
            $path = $dump->execute($this->option('connection') ?: null);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());
            report($e);

            return self::FAILURE;
        }

        $this->info('Backup written to '.$path.' ('.number_format((int) filesize($path) / 1024, 1).' KB)');

        return self::SUCCESS;
    }
}
