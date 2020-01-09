<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;
use T2G\Common\Models\UserLastLogin;
use T2G\Common\Repository\UserRepository;

class ImportUserLastLoginCommand extends Command
{
    const CONDITION_MINIMUM_LEVEL = 50;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:users:import_last_login {--path=} {--from=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to import users's last login from HWID logs";

    /**
     * @var \T2G\Common\Repository\UserRepository
     */
    protected $userRepository;

    /**
     * ImportUserLastLoginCommand constructor.
     *
     * @param \T2G\Common\Repository\UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processed = 0;
        $fromInt = 0;
        $from = $this->input->getOption('from');
        $path = $this->input->getOption('path');
        if (!$path) {
            $path = 'logs/savehwid';
        }
        $finder = new Finder();
        $finder->files()
            ->in(storage_path($path))
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
            $level = $texts[3];
            $server = $this->parseServer($logFile->getRelativePath());
            if ($level < self::CONDITION_MINIMUM_LEVEL) {
                continue;
            }
            $linesParsed[$username][$server] = [
                'last_login_date' => $loggedAt->format('Y-m-d H:i:s'),
                'server'          => $server,
            ];
        }

        $users = $this->userRepository->getUsersByNames(array_keys($linesParsed));
        $users = array_column($users, 'id', 'name');
        $records = [];
        foreach ($linesParsed as $username => $recordsPerServer) {
            if (!isset($users[strtolower($username)])) {
                continue;
            }
            foreach ($recordsPerServer as $server => $item) {
                $item['user_id'] = $users[strtolower($username)];
                $records[] = $item;
            }
        }
        UserLastLogin::insert($records);
        $processed += count($records);
        $this->output->text(sprintf("Completed importing `%s` records from log file %s", count($records), $logFile->getRealPath()));

        unset($records, $users, $linesParsed, $lines);

        return true;
    }

    /**
     * @param $filePath
     *
     * @return mixed
     */
    private function parseServer($filePath)
    {
        $paths = explode(DIRECTORY_SEPARATOR, $filePath);

        return strpos($paths[0], 's') !== false ? intval($paths[0][1]) : intval($paths[0]);
    }
}
