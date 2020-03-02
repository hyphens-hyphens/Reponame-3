<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Observers\UserObserver;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\JXApiClient;
use T2G\Common\Services\Kibana\MultipleLoginDetectionService;

class MonitorMultipleLoginCommand extends Command
{
    const MAX_ACCOUNT_PER_PC = 4;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:multiple_login {interval=15}';

    /**
     * @var \T2G\Common\Services\DiscordWebHookClient
     */
    protected $discord;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.multiple_login'));
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor multiple login on 1 PC based on Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $multipleLoginDetectionService = app(MultipleLoginDetectionService::class);
        $interval = $this->input->getArgument('interval');
        $from = new \DateTime("-{$interval} minutes");
        $results = $multipleLoginDetectionService->getMultipleLoginLogs($from);
        $report = [];
        foreach ($results->getHits() as $hit) {
            $row = $hit['_source'];
            if (empty($row['hwid_filtered'])) {
                continue;
            }
            $report[$row['jx_server'] . "|" . $row['log']['file']['path']][$row['hwid_filtered']][$row['user']][] = $row;
        }
        foreach ($report as $serverAndLogFile => $hwidArray) {
            $serverAndLogFileSplitted = explode('|', $serverAndLogFile);
            $server = $serverAndLogFileSplitted[0];
            $logFile = $serverAndLogFileSplitted[1];
            foreach ($hwidArray as $hwid => $userArray) {
                if (count($userArray) <= self::MAX_ACCOUNT_PER_PC ) {
                    continue;
                }
                $this->alertReport($server, $logFile, $hwid, $userArray);
            }
        }

    }

    private function alertReport($server, $logFile, $hwid, array $userArray)
    {
        $template = <<<'TEMPLATE'
        Server: S%s
        File: `%s`
        HWID: `%s`
        Dàn acc:
        %s
TEMPLATE;
        $listUsers = '';
        foreach ($userArray as $username => $charArray) {
            $existed = [];
            foreach ($charArray as $user) {
                if (in_array($user['user'], $existed)) {
                    continue;
                }
                $listUsers .= sprintf("- `%s (%s)` level %s, Map: %s (%s, %s) \n", $user['user'], $user['char'], $user['level'], $user['map'], $user['x'], $user['y']);
                $existed[] = $user['user'];
            }
        }
        $message = sprintf($template, $server, $logFile, $hwid, $listUsers);
        $this->discord->sendWithEmbed(
            "Cảnh báo Multi Login",
            $message,
            DiscordWebHookClient::EMBED_COLOR_NOTICE
        );
        sleep(1);
    }

    private function banUser($username)
    {
        $bannedPassword = 'keoxe_PM_bikhoa';
        $api = app(JXApiClient::class);
        $userRepository = app(UserRepository::class);
        /** @var \T2G\Common\Models\AbstractUser $user */
        $user = $userRepository->findUserByUsername($username);
        if ($user->getRawPassword() != $bannedPassword) {
            $userRepository->updatePassword($user, $bannedPassword);
            UserObserver::setIsDisabled(true);
            $api->setPassword($username, 'keoxe_PM_bikhoa');
        }
    }
}
