<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Database\PruneBackupsAction;
use Illuminate\Console\Command;

final class DeleteOldBackupsCommand extends Command
{
    protected $signature = 'db:delete {--keep=7 : Number of newest backups to keep}';

    protected $description = 'Delete database backups beyond the newest --keep files';

    public function handle(PruneBackupsAction $prune): int
    {
        $deleted = $prune->execute(max(1, (int) $this->option('keep')));

        $this->info('Deleted '.count($deleted).' old backup(s).');

        return self::SUCCESS;
    }
}
