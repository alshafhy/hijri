<?php

declare(strict_types=1);

namespace App\Actions\Database;

use Illuminate\Support\Facades\File;

final class PruneBackupsAction
{
    /**
     * Keeps the newest $keep backups and deletes the rest.
     *
     * @return list<string> deleted file names
     */
    public function execute(int $keep): array
    {
        $directory = storage_path(DumpDatabaseAction::DIRECTORY);
        if (! is_dir($directory)) {
            return [];
        }

        $files = collect(File::files($directory))
            ->filter(fn (\SplFileInfo $file): bool => str_ends_with($file->getFilename(), '.sql.gz'))
            ->sortByDesc(fn (\SplFileInfo $file): string => $file->getMTime().'|'.$file->getFilename())
            ->values();

        $deleted = [];
        foreach ($files->slice(max(0, $keep)) as $file) {
            if (@unlink($file->getPathname())) {
                $deleted[] = $file->getFilename();
            }
        }

        return $deleted;
    }
}
