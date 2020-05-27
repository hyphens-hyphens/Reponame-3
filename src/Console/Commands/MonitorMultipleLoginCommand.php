<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\AbstractKibanaService;
use T2G\Common\Services\Kibana\LogLanQueryService;
use T2G\Common\Services\Kibana\MultipleLoginDetectionService;
use T2G\Common\Util\CommonHelper;

class MonitorMultipleLoginCommand extends AbstractJXCommand
{
    const MAX_ACCOUNT_PER_PC = 4;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:multiple_login {minutes=15} {--interval=0}';

    /**
     * @var \T2G\Common\Services\DiscordWebHookClient
     */
    protected $discord;

    protected $excluded = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.multiple_login'));
        $this->excluded = config('t2g_common.jx_monitor.multi_login_excluded_accounts');
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
        $minutes = $this->input->getArgument('minutes');
        $from = new \DateTime("-{$minutes} minutes");
        $interval = intval($this->input->getOption('interval'));
        $interval = $interval > 0 ? \DateInterval::createFromDateString("{$interval} minutes") : null;
        $results = $multipleLoginDetectionService->getMultipleLoginLogs($from, $interval, AbstractKibanaService::MAX_RESULTS_WINDOW);
        $report = [];
        foreach ($results->getHits() as $hit) {
            $row = $hit['_source'];
            if (empty($row['hwid']) || in_array($row['user'], $this->excluded)) {
                continue;
            }

            $filteredHwid = CommonHelper::getFilteredHwid($row['hwid']);
            $key = $row['jx_server'] . "|" . $row['log']['file']['path'];
            $report[$key][$filteredHwid][] = $row;
        }
        foreach ($report as $serverAndLogFile => $hwidArray) {
            $serverAndLogFileSplitted = explode('|', $serverAndLogFile);
            $server = $serverAndLogFileSplitted[0];
            $logFile = $serverAndLogFileSplitted[1];
            foreach ($hwidArray as $hwid => $userArray) {
                if (count($userArray) <= self::MAX_ACCOUNT_PER_PC ) {
                    continue;
                }
                $url = $this->saveDataFile($userArray, $server);
                $this->alertReport($server, $logFile, $hwid, $userArray, $url);
                sleep(1);
            }
        }

    }

    private function alertReport($server, $logFile, $hwid, array $userArray, $url)
    {
        $file = explode('/', $logFile);
        $file = last($file);
        $template = <<<'TEMPLATE'
        Server: S%s
        File: `%s`
        Link: %s
        HWID: `%s`
        Dàn acc:
        %s
TEMPLATE;
        $listUsers = '';
        foreach ($userArray as $user) {
            $existed = [];
            if (in_array($user['user'], $existed)) {
                continue;
            }
            $listUsers .= sprintf(
                "- `%s`, `%s (%s)`, lv %s, %s\n",
                $user['hwid'],
                $user['user'],
                $user['char'],
                $user['level'],
                $user['map']
            );
            $existed[] = $user['user'];
        }
        $message = sprintf($template, $server, $file, $url, $hwid, $listUsers);
        $this->discord->sendWithEmbed(
            "Cảnh báo Multi Login",
            str_limit($message, 2040),
            DiscordWebHookClient::EMBED_COLOR_NOTICE
        );
        sleep(1);
    }

    /**
     * @param array $listAcc
     * @param       $server
     *
     * @return string
     * @throws \Exception
     */
    private function saveDataFile(array $listAcc, $server)
    {
        $usernames = array_column($listAcc, 'user');
        $firstAcc = array_first($listAcc);
        $listIp = app(LogLanQueryService::class)->getIpLanByUsernames($usernames, $server, new \DateTime($firstAcc['@timestamp']));
        $now = time();
        $filename = "multi_login_{$now}";
        $file = storage_path('app/console_log/' . $filename);
        file_put_contents($file, json_encode([
            'server' => $firstAcc['jx_server'],
            'accs'   => $listAcc,
            'ips'    => $listIp,
        ]));

        return route('voyager.console_log_viewer.multi_login', ['t' => $now]);
    }
}
