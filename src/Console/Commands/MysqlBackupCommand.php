<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

class MysqlBackupCommand extends Command
{
    protected $targetDir = "/root/backup/";

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:mysql:backup {connection} {--targetDir=} {--keep=10} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup a Mysql database by the specific connection name.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $connectionName = $this->argument('connection');
        try {
            $connection = $this->getMysqlConnection($connectionName);
            $output = '';
            if ($connection && $this->makeBackup($connectionName, $connection, $output)) {
                // clean up old backup
                $this->cleanup($connection['database'], $this->option('keep'));
            } else {
                $this->output->error("Failed in creating backup " . PHP_EOL  . implode(PHP_EOL, $output));
            }
        } catch (\InvalidArgumentException $e) {
            $this->output->error("Cannot connect to the specific connection");
        }
    }

    /**
     * @param $database
     *
     * @return string
     */
    private function getTargetFilePath($database)
    {
        $time = date('YmdHi');

        return $this->targetDir . "{$database}_{$time}.sql";
    }

    /**
     * @param $connectionName
     * @param $connection
     * @param $output
     *
     * @return bool
     */
    private function makeBackup($connectionName, $connection, &$output)
    {
        $this->output->title("Process backing up connection `{$connectionName}`, database: `{$connection['database']}`");
        if ($targetDir = $this->option('targetDir')) {
            $this->targetDir = $targetDir;
        }
        $targetFile = $this->getTargetFilePath($connection['database']);
        exec("mysqldump -p{$connection['password']} -u {$connection['username']} -h {$connection['host']} -P {$connection['port']} {$connection['database']} > {$targetFile}", $output, $result);
        if ($result === 0) {
            $this->output->success("Database connection `{$connectionName}` was backed up successfully as `{$targetFile}`.");

            return true;
        }
        $this->output->warning(implode("\r\n", $output));

        return false;
    }

    /**
     * @param     $database
     * @param int $keep
     *
     * @return bool
     */
    private function cleanup($database, $keep = 10)
    {
        if ($keep <= 0) {
            return false;
        }
        $backupFilesPattern = '/' . $database . '_[0-9]+\.sql/';
        $finder = new Finder();
        try {
            $finder->files()
                ->in($this->targetDir)
                ->sortByModifiedTime()
                ->path($backupFilesPattern);
        } catch (\InvalidArgumentException $e) {
            $this->output->warning($e->getMessage());

            return false;
        }

        $totalFiles = $finder->count();
        if ($totalFiles <= $keep) {
            return false;
        }
        $this->output->text("Processing clean up old backup files, `keep={$keep}`");
        $deletedCounter = 0;
        /** @var \Symfony\Component\Finder\SplFileInfo $file */
        foreach ($finder as $file) {
            if ($totalFiles - $deletedCounter++ <= $keep) {
                break;
            }
            $this->output->text("Deleting " . $file->getRealPath());
            unlink($file->getRealPath());
        }

        return true;
    }

    /**
     * make sure we can access the database with specific config
     *
     * @param $connectionName
     *
     * @return array
     */
    private function getMysqlConnection($connectionName)
    {
        $connections = config('database.connections');
        if (!$connectionName || !isset($connections[$connectionName])) {
            throw new \InvalidArgumentException();
        }
        /** @var \Illuminate\Database\DatabaseManager $db */
        $db = app(\Illuminate\Database\DatabaseManager::class);
        $db->connection($connectionName);

        return $connections[$connectionName];
    }
}
