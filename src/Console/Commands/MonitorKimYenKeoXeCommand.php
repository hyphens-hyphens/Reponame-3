<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\KimYenKeoXeDetectionService;

class MonitorKimYenKeoXeCommand extends AbstractJXCommand
{
    const ACTION_LEAVE_MAP     = 'LeaveMap';
    const ACTION_MOVE_TO       = 'MoveTo';

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
                foreach ($item as $k => $row) {
                    $key = sprintf("%s:%s", $mainAcc['jx_server'], bin2hex($mainAcc['user']));
                    $row['weight'] = $this->redis->hincrby($key, bin2hex($row['user']), 1);
                    $secondaryAccs[] = $row;
                    if ($k == 0) {
                        // set expire at 1 month later
                        $this->redis->expire($key, 30 * 24 * 3600);
                    }
                }
                $this->alertReport($mainAcc, $secondaryAccs);
            }
        }
    }

    private function alertReport(array $mainAcc, array $secondaryAccs)
    {
        $template = <<<'TEMPLATE'
        Server: S%s , Thời gian: `%s`
        Acc chính: `%s (%s)` level %s. Map: `%s (%s)` -> `%s (%s)`
        HWIDs:
        - %s
        Dàn acc:
        %s
TEMPLATE;
        $listUsers = [];
        $hwidArray = [$this->getFilteredHwid($mainAcc['hwid'])];
        foreach ($secondaryAccs as $k => $acc) {
            $filteredHwid = $this->getFilteredHwid($acc['hwid']);
            if (!in_array($filteredHwid, $hwidArray)) {
                $hwidArray[] = $filteredHwid;
            }
            $index = array_search($filteredHwid, $hwidArray);
            $wrapper = $this->getMarkdownWrapper($index);
            $hwid = $wrapper . $filteredHwid . strrev($wrapper);
            $key = $filteredHwid . $k;
            $listUsers[$key] = sprintf(
                "- `%s (%s)`, %s, `%s lần`  \n",
                $acc['user'],
                $acc['char'],
//                $acc['level'],
                $hwid,
                $acc['weight']
            );
        }
        ksort($listUsers);
        $message = sprintf(
            $template,
            $mainAcc['jx_server'],
            date('d-m-Y H:i:s', $mainAcc['enter_at']),
            $mainAcc['user'],
            $mainAcc['char'],
            $mainAcc['level'],
            $mainAcc['map_name'],
            $mainAcc['map_id'],
            $mainAcc['move_map_name'],
            $mainAcc['move_map_id'],
            implode("\n- ", $hwidArray),
            implode('', array_values($listUsers))
        );
        $this->discord->sendWithEmbed(
            "Cảnh báo Kéo xe Kim Yến",
            $message,
            DiscordWebHookClient::EMBED_COLOR_NOTICE
        );
        sleep(1);
    }

    /**
     * @param $index
     *
     * @return mixed
     */
    private function getMarkdownWrapper($index)
    {
        $styleWrapper = [
            '*', '**', '***', '__', '__*', '__**', '__***'
        ];

        return $index < count($styleWrapper) ? $styleWrapper[$index] : '*';
    }
}
