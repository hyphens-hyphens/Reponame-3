<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Observers\UserObserver;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\JXApiClient;
use T2G\Common\Services\Kibana\AccountService;
use T2G\Common\Services\Kibana\MultiplePCDetectionService;

class MonitorMultiplePCCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:multiple_pc_pm {interval=5}';

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
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.multiple_pc'));
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor multiple PC team up in private chat based on Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $multiplePcDetectionService = app(MultiplePCDetectionService::class);
        $accountService = app(AccountService::class);
        $interval = $this->input->getArgument('interval');
        $from = new \DateTime("-{$interval} minutes");
        $results = $multiplePcDetectionService->getMultiplePCTeamUpInPrivateChat($from);
        $aggs = $results->getAggs();
        $data = $report = [];
        foreach ($aggs['filter_data']['server']['buckets'] as $bucket) {
            $server = $bucket['key'];
            $char1Buckets = $bucket['char1']['buckets'];
            foreach ($char1Buckets as $char1Bucket) {
                $char1 = $char1Bucket['key'];
                $char2Buckets = $char1Bucket['char2']['buckets'];
                foreach ($char2Buckets as $char2Bucket) {
                    $char2 = $char2Bucket['key'];
                    $data[$server][$char1][] = $char2;
                }
            }
        }
        foreach ($data as $server => $chars) {
            foreach ($chars as $char => $char2Arr) {
                $user = $accountService->getUsernameByChar($server, $char);
                $users2 = [];
                foreach ($char2Arr as $char2) {
                    $user2 = $accountService->getUsernameByChar($server, $char2);
                    $users2[] = $user2;
                }
                $report[$server][] = [
                    'user'   => $user,
                    'users2' => $users2,
                ];
            }
        }
        if (!$report) {
            return null;
        }

        $this->alertReport($report);
    }

    private function alertReport(array $report)
    {
        $template = <<<'TEMPLATE'
        Server: S%s
        Chủ xe: `%s (%s)` level %s
        Dàn xe:
        %s
TEMPLATE;
        $listUsers = '';
        foreach ($report as $server => $items) {
            foreach ($items as $item) {
                $user = $item['user'];
                $this->banUser($user['user']);
                $listUsers = '';
                foreach ($item['users2'] as $user2) {
                    $listUsers .= sprintf("- `%s (%s)` level %s \n", $user2['user'], $user2['char'], $user2['level']);
                }
            }
            $message = sprintf($template, $server, $user['user'], $user['char'], $user['level'], $listUsers);
            $this->discord->sendWithEmbed(
                "Cảnh báo kéo xe Chat mật",
                $message,
                DiscordWebHookClient::EMBED_COLOR_NOTICE
            );
            sleep(1);
        }
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
