<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\KimYenKeoXeDetectionService;
use T2G\Common\Services\Kibana\LogLanQueryService;
use T2G\Common\Util\CommonHelper;

class MonitorKimYenKeoXeCommand extends AbstractJXCommand
{
    const ACTION_LEAVE_MAP = 'LeaveMap';
    const ACTION_MOVE_TO   = 'MoveTo';
    const WEIGHT_THRESHOLD = 2;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:kimyen_keoxe {interval=15}';

    /**
     * @var \T2G\Common\Services\DiscordWebHookClient
     */
    protected $discord;

    /**
     * @var \Illuminate\Support\Facades\Redis
     */
    protected $redis;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.kimyen'));
        $this->redis = app('redis.connection');
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor multiple PC team up based on Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $multipleLoginDetectionService = app(KimYenKeoXeDetectionService::class);
        $interval = $this->input->getArgument('interval');
        $from = new \DateTime("-{$interval} minutes");
        $results = $multipleLoginDetectionService->getMoveMapLogs($from);
        $report = $queue = [];
        foreach ($results->getHits() as $hit) {
            $row = $hit['_source'];
            if (empty($row['action']) || empty($row['map_id'])) {
                continue;
            }
            // Server|Username|LeaveMap
            $queueKey = sprintf("%s|%s|%s", $row['jx_server'], $row['user'], self::ACTION_LEAVE_MAP);
            if ($row['action'] == self::ACTION_LEAVE_MAP) {
                $queue[$queueKey][] = $row;
            } else {
                $moveMapItem = $row;
                if (!isset($queue[$queueKey])) {
                    continue;
                }
                foreach ($queue[$queueKey] as $index => $leaveMapItem) {
                    $leaveAt = strtotime($leaveMapItem['@timestamp']);
                    $enterAt = strtotime($moveMapItem['@timestamp']);
                    $timeSub = $enterAt - $leaveAt;
                    if ($leaveMapItem['user'] == $moveMapItem['user'] && $timeSub > 0 &&  $timeSub <= 2) {
                        // match route Server|LeaveMap|LeaveMap_ID|MoveTo|MoveTo_MapID
                        $key = sprintf(
                            "%s|%s|%s|%s|%s",
                            $moveMapItem['jx_server'],
                            self::ACTION_LEAVE_MAP,
                            $leaveMapItem['map_id'],
                            self::ACTION_MOVE_TO,
                            $moveMapItem['map_id']
                        );
                        $leaveMapItem['leave_at'] = $leaveAt;
                        $leaveMapItem['enter_at'] = $enterAt;
                        $leaveMapItem['move_map_name'] = $moveMapItem['map_name'];
                        $leaveMapItem['move_map_id'] = $moveMapItem['map_id'];
                        $report[$key][] = $leaveMapItem;
                        unset($queue[$queueKey][$index]);
                        break;
                    }
                }
            }
        }

        $final = [];
        foreach ($report as $key => $rows) {
            foreach ($rows as $index => $row) {
                if ($index == 0) {
                    $flag = $row;
                    $final[$key . "|" . $flag['enter_at']][] = $flag;
                    continue;
                }
                if ($row['enter_at'] - $flag['enter_at'] < 2){
                    $final[$key . "|" . $row['enter_at']][] = $row;
                } else {
                    // re-set $flag
                    $flag = $row;
                    $final[$key. "|" . $flag['enter_at']][] = $flag;
                }
            }
        }
        foreach ($final as $key => $item) {
            if (count($item) > 4) {
                /*
                 * ex
                 * 4 => array:10 [
                        "jx_server" => 3
                        "@timestamp" => "2020-03-04T15:37:35.000Z"
                        "level" => 178
                        "map_id" => 204
                        "map_name" => "Phi Thiªn ®éng"
                        "char" => "ÙCËuÙótÙ"
                        "action" => "LeaveMap"
                        "user" => "tanryo251"
                        "leave_at" => 1583336255
                        "enter_at" => 1583336256
                        "leave_map_id" => 2
                        "leave_map_name" => "Lâm An"
                      ]
                 */
                $mainAcc = array_shift($item);
                $secondaryAccs = [];
                $alert = false;
                $previousRow = null;
                $server = $mainAcc['jx_server'];
                foreach ($item as $k => $row) {
                    $weights = $this->increaseWeight($server, $mainAcc['user'], $row['user']);
                    $row['weight'] = $weights[0];
                    $secondaryAccs[] = $row;
                    if ($row['weight'] > self::WEIGHT_THRESHOLD) {
                        $alert = true;
                    }
                    if ($previousRow) {
                        $this->increaseWeight($server, $previousRow['user'], $row['user']);
                    }
                    $previousRow = $row;
                }
                if ($alert) {
                    $this->alertReport($mainAcc, $secondaryAccs);
                }
            }
        }
    }

    private function alertReport(array $mainAcc, array $secondaryAccs)
    {
        $template = <<<'TEMPLATE'
Server: S%s , Thời gian: `%s`
Map: `%s (%s)` -> `%s (%s)`
Link: %s
Dàn acc:
%s
TEMPLATE;
        $mainAcc['weight'] = 0;
        $listUsers = [];
        array_unshift($secondaryAccs, $mainAcc);
        foreach ($secondaryAccs as $k => $acc) {
            $filteredHwid = CommonHelper::getFilteredHwid($acc['hwid']);
            $key = $filteredHwid . ($k + 1);
            $listUsers[$key] = sprintf(
                "-`%s (%s)`, %s, LV %s, `%s`\n",
                $acc['user'],
                $acc['char'],
                $acc['hwid'],
                $acc['level'],
                $acc['weight'] > 0 ? "{$acc['weight']} lần" : "acc chính"
            );
        }
        ksort($listUsers);
        $url = $this->saveDataFile($mainAcc, $secondaryAccs);

        $message = sprintf(
            $template,
            $mainAcc['jx_server'],
            date('d-m-Y H:i:s', $mainAcc['enter_at']),
            $mainAcc['map_name'],
            $mainAcc['map_id'],
            $mainAcc['move_map_name'],
            $mainAcc['move_map_id'],
            $url,
            implode('', array_values($listUsers))
        );
        $this->discord->sendWithEmbed(
            "Cảnh báo Kéo xe Kim Yến",
            str_limit($message, 2040),
            DiscordWebHookClient::EMBED_COLOR_NOTICE
        );
        sleep(1);
    }

    /**
     * @param array $mainAcc
     * @param array $listAccs
     *
     * @return string
     * @throws \Exception
     */
    private function saveDataFile(array $mainAcc, array $listAccs)
    {
        $token = md5($mainAcc['user'] . time());
        $usernames = array_column($listAccs, 'user');
        $listIp = app(LogLanQueryService::class)->getIpLanByUsernames($usernames, $mainAcc['jx_server'], new \DateTime('@' . $mainAcc['enter_at']));
        $filename = "kimyen_{$token}";
        $file = storage_path('app/console_log/' . $filename);
        file_put_contents($file, json_encode([
            'mainAcc'  => $mainAcc,
            'listAccs' => $listAccs,
            'ips'      => $listIp,
        ]));

        return route('voyager.console_log_viewer.kimyen', ['t' => $token]);
    }

    /**
     * @param $server
     * @param $user1
     * @param $user2
     *
     * @return array
     */
    private function increaseWeight($server, $user1, $user2)
    {
        $keys = [
            sprintf("%s:%s", $server, bin2hex($user1)) => $user2,
            sprintf("%s:%s", $server, bin2hex($user2)) => $user1
        ];
        $retval = [];
        foreach ($keys as $key => $hash) {
            $retval[] = $this->redis->hincrby($key, bin2hex($hash), 1);
            $this->redis->expire($key, 30 * 24 * 3600);
        }

        return $retval;
    }
}
