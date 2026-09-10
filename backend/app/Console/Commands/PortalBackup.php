<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
class PortalBackup extends Command
{
    protected $signature = 'portal:backup';
    protected $description = 'Create a private SQLite or MySQL database backup with a checksum.';
    public function handle(): int
    {
        $directory = config('services.backup_directory', storage_path('app/private/backups'));
        if (!is_dir($directory)) mkdir($directory, 0700, true);
        $connection = config('database.default');
        $config = config('database.connections.'.$connection);
        $file = $directory.'/database-'.now()->format('Ymd-His').'-'.bin2hex(random_bytes(4));
        try {
            if ($config['driver'] === 'sqlite') {
                $file .= '.sqlite';
                DB::statement("VACUUM INTO '".str_replace("'", "''", $file)."'");
            } elseif ($config['driver'] === 'mysql') {
                $file .= '.sql'; $handle = fopen($file, 'xb');
                try {
                    $process = new Process([config('services.mysqldump_binary', 'mysqldump'), '--single-transaction', '--quick', '--skip-lock-tables', '--host='.$config['host'], '--port='.$config['port'], '--user='.$config['username'], $config['database']], null, ['MYSQL_PWD' => $config['password']]);
                    $process->setTimeout(600);
                    $process->run(function ($type, $buffer) use ($handle) { if ($type === Process::OUT) fwrite($handle, $buffer); });
                    if (!$process->isSuccessful()) throw new \RuntimeException('Database dump failed. Check database access and MYSQLDUMP_BINARY.');
                } finally { fclose($handle); }
            } else throw new \RuntimeException('Configure a provider backup for this database driver.');
            file_put_contents($file.'.sha256', hash_file('sha256', $file));
            $this->info('Backup created: '.$file); return self::SUCCESS;
        } catch (\Throwable $e) { if (is_file($file)) unlink($file); $this->error($e->getMessage()); return self::FAILURE; }
    }
}
