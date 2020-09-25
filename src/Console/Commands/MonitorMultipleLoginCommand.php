<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\AbstractKibanaService;
use T2G\Common\Services\Kibana\AccountService;
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
        $usernames = [];
        $logTime = null;
        foreach ($results->getHits() as $hit) {
            if (in_array($hit['_source']['user'], $usernames)) {
                continue;
            }
            $usernames[] = $hit['_source']['user'];
            if (!$logTime) {
                $logTime = new \DateTime($hit['_source']['@timestamp']);
            }
        }

        $hwidArray = app(AccountService::class)->getHwidByUsernames($usernames, $logTime);
        foreach ($results->getHits() as $hit) {
            $row = $hit['_source'];
            $hwid = $hwidArray[$row['user']] ?? null;
            if (empty($hwid) || in_array($row['user'], $this->excluded)) {
                continue;
            }

            $filteredHwid = CommonHelper::getFilteredHwid($hwid);
            $key = $row['jx_server'] . "|" . $row['log']['file']['path'];
            $report[$key][$filteredHwid][] = $row;
        }
        foreach ($report as $serverAndLogFile => $hwids) {
            $serverAndLogFileSplitted = explode('|', $serverAndLogFile);
            $server = $serverAndLogFileSplitted[0];
            $logFile = $serverAndLogFileSplitted[1];
            foreach ($hwids as $hwid => $userArray) {
                if (count($userArray) <= self::MAX_ACCOUNT_PER_PC ) {
                    continue;
                }
                $listIps = $this->getListIps($userArray, $server);
                if ($this->isSkippedByIps($listIps)) {
                    continue;
                }
                $url = $this->saveDataFile($userArray, $hwidArray, $listIps);
                $this->alertReport($server, $logFile, $hwid, $userArray, $hwidArray, $url);
                sleep(1);
            }
        }

    }

    /**
     * @param       $server
     * @param       $logFile
     * @param       $filteredHwid
     * @param array $userArray
     * @param array $hwidArray
     * @param       $url
     *
     * @throws \Exception
     */
    private function alertReport($server, $logFile, $filteredHwid, array $userArray, array $hwidArray, $url)
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
            $hwid = $hwidArray[$user['user']] ?? null;
            if (!$hwid || in_array($user['user'], $existed)) {
                continue;
            }
            $listUsers .= sprintf(
                "- `%s`, `%s (%s)`, lv %s, %s\n",
                $hwid,
                $user['user'],
                $user['char'],
                $user['level'],
                $user['map']
            );
            $existed[] = $user['user'];
        }
        $message = sprintf($template, $server, $file, $url, $filteredHwid, $listUsers);
        $this->discord->sendWithEmbed(
            "Cảnh báo Multi Login",
            $message,
            DiscordWebHookClient::EMBED_COLOR_NOTICE
        );
        sleep(1);
    }

    /**
     * @param array $listAcc
     * @param array $hwidArray
     * @param       $listIp
     *
     * @return string
     */
    private function saveDataFile(array $listAcc, array $hwidArray, $listIp)
    {
        $firstAcc = array_first($listAcc);
        $now = time();
        $filename = "multi_login_{$now}";
        $file = storage_path('app/console_log/' . $filename);
        file_put_contents($file, json_encode([
            'server'       => $firstAcc['jx_server'],
            'hwidFiltered' => CommonHelper::getFilteredHwid($hwidArray[$firstAcc['user']] ?? ''),
            'accs'         => $listAcc,
            'ips'          => $listIp,
            'hwids'        => $hwidArray,
            'version'      => '_v2',
        ]));

        return route('voyager.console_log_viewer.multi_login', ['t' => $now]);
    }

    /**
     * @param $listIps
     *
     * @return bool
     */
    private function isSkippedByIps($listIps)
    {
        $combinedIps = [];
        foreach ($listIps as $acc => $ips) {
            $combinedIp = implode(', ', $ips);
            $combinedIps[$combinedIp][] = $acc;
        }
        foreach ($combinedIps as $ip => $accs) {
            if (count($accs) > self::MAX_ACCOUNT_PER_PC) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param $userArray
     * @param $server
     *
     * @return array
     * @throws \Exception
     */
    private function getListIps($userArray, $server)
    {
        $firstAcc = array_first($userArray);
        $usernames = array_column($userArray, 'user');
        $listIp = app(LogLanQueryService::class)->getIpLanByUsernames($usernames, $server, new \DateTime($firstAcc['@timestamp']));

        return $listIp;
    }
}
