<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use T2G\Common\Models\UserLastLogin;
use T2G\Common\Repository\UserRepository;

class ImportUserLastLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:users:import_last_login {--from=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to import users's last login from HWID logs";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processed = 0;
        $fromInt = 0;
        $from = $this->input->getOption('from');
        $finder = new Finder();
        $finder->files()
            ->in(storage_path('logs/savehwid'))
            ->name('*.txt')
            ->sortByName()
        ;
        if ($from) {
            try {
                $date = new \DateTime($from);
                $fromInt = intval($date->format('Ymd'));
            } catch (\Exception $e) {
                $this->output->error("Option `from` does not have a valid format `Y-m-d`");
                exit();
            }
        }
        $this->output->text("Importing users last login from `savehwid` log files");
        /** @var \Symfony\Component\Finder\SplFileInfo $item */
        foreach ($finder as $logFile) {
            $fileDate = intval(substr($logFile->getFilename(), 0, -4));
            if ($fileDate < $fromInt) {
                continue;
            }
            $this->importFromLogFile($logFile, $processed);
        }
        $this->output->success("Imported {$processed} records.");
    }

    /**
     * @param \Symfony\Component\Finder\SplFileInfo $logFile
     * @param int                                   $processed
     *
     * @return int
     */
    private function importFromLogFile(\Symfony\Component\Finder\SplFileInfo $logFile, &$processed = 0)
    {
        $lines = file($logFile->getRealPath());
        if (!$lines) {
            return false;
        }
        $linesParsed = [];
        foreach ($lines as $line) {
            $line = trim($line);
            $texts = explode("\t", $line);
            $loggedAt = \DateTime::createFromFormat("d/m/Y_H:i:s", $texts[0]);
            $username = $texts[1];
            $hwid = $texts[4];
            $linesParsed[$username] = [
                'loggedAt' => $loggedAt,
                'hwid'     => $hwid,
            ];
        }

        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);
        $users = $userRepository->getUsersByNames(array_keys($linesParsed));
        $users = array_column($users, 'id', 'name');
        $model = app(UserLastLogin::class);
        $records = [];
        foreach ($linesParsed as $username => $item) {
            if (!isset($users[strtolower($username)])) {
                continue;
            }
            $records[] = [
                'user_id'         => $users[strtolower($username)],
                'last_login_date' => $item['loggedAt'],
                'hwid'            => $item['hwid'],
            ];
        }
        \DB::table($model->getTable())->insert($records);
        $processed += count($records);
        $this->output->text(sprintf("Completed importing `%s` records from log file %s", count($records), $logFile->getRealPath()));

        return true;
    }
}
