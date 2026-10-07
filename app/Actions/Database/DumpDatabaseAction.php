<?php

declare(strict_types=1);

namespace App\Actions\Database;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Process\Process;

final class DumpDatabaseAction
{
    public const DIRECTORY = 'app/private/backups';

    private const COMPLETED_MARKER = '-- Dump completed';

    /**
     * Streams a gzipped mysqldump of the given connection into storage/app/private/backups.
     *
     * @return string absolute path of the backup file
     */
    public function execute(?string $connection = null): string
    {
        $connection ??= (string) config('database.default');
        $config = (array) config("database.connections.{$connection}");

        if (! in_array($config['driver'] ?? null, ['mysql', 'mariadb'], true)) {
            throw new RuntimeException("Connection [{$connection}] is not a MySQL connection.");
        }

        File::ensureDirectoryExists(storage_path(self::DIRECTORY), 0750);
        $target = storage_path(self::DIRECTORY.'/'.$config['database'].'-'.now()->format('Y-m-d_His').'.sql.gz');
        $defaults = $this->writeDefaultsFile($config);

        $gz = gzopen($target, 'wb6');
        if ($gz === false) {
            @unlink($defaults);
            throw new RuntimeException("Unable to open [{$target}] for writing.");
        }

        $tail = '';
        $process = new Process([
            'mysqldump',
            '--defaults-extra-file='.$defaults,
            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--no-tablespaces',
            (string) $config['database'],
        ]);
        $process->setTimeout(3600);

        try {
            $process->run(function (string $type, string $buffer) use ($gz, &$tail): void {
                if ($type === Process::OUT) {
                    gzwrite($gz, $buffer);
                    $tail = substr($tail.$buffer, -200);
                }
            });
        } finally {
            gzclose($gz);
            @unlink($defaults);
        }

        if (! $process->isSuccessful() || ! str_contains($tail, self::COMPLETED_MARKER)) {
            @unlink($target);
            throw new RuntimeException('mysqldump failed: '.trim($process->getErrorOutput()));
        }

        return $target;
    }

    /**
     * Keeps the password out of the process list.
     *
     * @param  array<string, mixed>  $config
     */
    private function writeDefaultsFile(array $config): string
    {
        $path = tempnam(sys_get_temp_dir(), 'dump');
        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary credentials file.');
        }
        chmod($path, 0600);

        $quote = static fn (mixed $value): string => '"'.addcslashes((string) $value, '\\"').'"';
        $lines = ['[client]', 'user='.$quote($config['username'] ?? ''), 'password='.$quote($config['password'] ?? '')];
        if (! empty($config['unix_socket'])) {
            $lines[] = 'socket='.$quote($config['unix_socket']);
        } else {
            $lines[] = 'host='.$quote($config['host'] ?? '127.0.0.1');
            $lines[] = 'port='.(int) ($config['port'] ?? 3306);
        }

        file_put_contents($path, implode("\n", $lines)."\n");

        return $path;
    }
}
