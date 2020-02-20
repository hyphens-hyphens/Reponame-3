<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\ElasticSearch\SearchResult;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\AccountService;
use T2G\Common\Services\Kibana\TradingMonitoringService;

class MonitorJXMoneyTradingCommand extends Command
{
    const K_VAN = 10000000;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:money_trading {threshold=30}';

    /**
     * @var \T2G\Common\Services\DiscordWebHookClient
     */
    protected $discord;

    /**
     * @var \Illuminate\Foundation\Application|mixed|\T2G\Common\Services\Kibana\TradingMonitoringService
     */
    protected $kibana;

    /**
     * @var \Illuminate\Foundation\Application|mixed|\T2G\Common\Services\Kibana\AccountService
     */
    protected $accountService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.police'));
        $this->kibana  = app(TradingMonitoringService::class);
        $this->accountService = app(AccountService::class);
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor JX money trading from Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $threshold = intval($this->input->getArgument('threshold'));
        $from = new \DateTime("-30 minutes");
        $this->output->text("Checking money trading from `Kibana logs`");
        $this->output->title("Processing from " . $from->format('c'));

        $this->monitorMoneyOwnerTrading($from, $threshold);
        $this->monitorMoneyReceiverTrading($from, $threshold);

        $this->output->success("Done processing.");
    }

    private function monitorMoneyOwnerTrading(\DateTime $from, int $threshold)
    {
        $results = $this->kibana->getMoneyTradingByOwnerLogs($from);
        $report = $this->prepareReportOwner($results, $threshold);
        if (!$report) {
            return null;
        }
        $this->alertReportOwner($report, $threshold);
    }

    private function monitorMoneyReceiverTrading(\DateTime $from, int $threshold)
    {
        $results = $this->kibana->getMoneyTradingByReceiverLogs($from);
        $report = $this->prepareReportReceiver($results, $threshold);
        if (!$report) {
            return null;
        }
        $this->alertReportReceiver($report, $threshold);
    }

    private function alertReportOwner(array $report, $threshold)
    {
        // todo: refactor alertReportOwner and alertReportReceiver to 1 function
        $template = <<<'TEMPLATE'
        Server: S%s
        Người giao dịch: `%s (%s)` level %s
        Người nhận:
        %s
TEMPLATE;
        $listUsers = '';
        foreach ($report as $server => $items) {
            foreach ($items as $item) {
                $user = $item['user'];
                $listUsers = '';
                foreach ($item['users2'] as $user2) {
                    $listUsers .= sprintf("- `%s (%s)` %s kVạn \n", $user2['user'], $user2['char'], round($user2['amount'] / self::K_VAN));
                }
            }
            $message = sprintf($template, $server, $user['user'], $user['char'], $user['level'], $listUsers);
            $this->discord->sendWithEmbed(
                "Log giao dịch tiền vạn SLL (> {$threshold} kVạn)",
                $message,
                DiscordWebHookClient::EMBED_COLOR_NOTICE
            );
            sleep(1);
        }
    }

    private function prepareReportOwner(SearchResult $results, int $threshold)
    {
        // todo: refactor prepareReportOwner and prepareReportReceiver to 1 function
        $data = $report = [];
        $aggs = $results->getAggs();
        if (!isset($aggs['filter_aggs']['server']['buckets'])) {
            return false;
        }
        foreach ($aggs['filter_aggs']['server']['buckets'] as $bucket) {
            $server = $bucket['key'];
            $char1Buckets = $bucket['char1']['buckets'];
            foreach ($char1Buckets as $char1Bucket) {
                $char1 = $char1Bucket['key'];
                $char2Buckets = $char1Bucket['char2']['buckets'];
                foreach ($char2Buckets as $char2Bucket) {
                    $char2 = $char2Bucket['key'];
                    if ($char2Bucket['total_money']['value'] > ($threshold * self::K_VAN)) {
                        $data[$server][$char1][$char2] = $char2Bucket['total_money']['value'];
                    }
                }
            }
        }
        foreach ($data as $server => $chars) {
            foreach ($chars as $char => $char2Arr) {
                $user = $this->accountService->getUsernameByChar($server, $char);
                $users2 = [];
                foreach ($char2Arr as $char2 => $amount) {
                    $user2 = $this->accountService->getUsernameByChar($server, $char2);
                    $user2['amount'] = $amount;
                    $users2[] = $user2;
                }
                $report[$server][] = [
                    'user'   => $user,
                    'users2' => $users2,
                ];
            }
        }

        return $report;
    }

    private function alertReportReceiver(array $report, $threshold)
    {
        $template = <<<'TEMPLATE'
        Server: S%s
        Người nhận: `%s (%s)` level %s tổng cộng %s kVạn
        Người giao dịch:
        %s
TEMPLATE;
        $listUsers = '';
        $total = 0;
        foreach ($report as $server => $items) {
            foreach ($items as $item) {
                $total = 0;
                $user2 = $item['user2'];
                $listUsers = '';
                foreach ($item['users1'] as $user1) {
                    $total += $user1['amount'];
                    $listUsers .= sprintf("- `%s (%s)` %s kVạn \n", $user1['user'], $user1['char'], round($user1['amount'] / self::K_VAN));
                }
            }
            $message = sprintf($template, $server, $user2['user'], $user2['char'], $user2['level'], round($total / self::K_VAN), $listUsers);
            $this->discord->sendWithEmbed(
                "Log nhận xu SLL (> {$threshold} kVạn)",
                $message,
                DiscordWebHookClient::EMBED_COLOR_NOTICE
            );
            sleep(1);
        }
    }

    private function prepareReportReceiver(SearchResult $results, int $threshold)
    {
        $data = $report = [];
        $aggs = $results->getAggs();
        if (!isset($aggs['filter_aggs']['server']['buckets'])) {
            return false;
        }
        foreach ($aggs['filter_aggs']['server']['buckets'] as $bucket) {
            $server = $bucket['key'];
            $char2Buckets = $bucket['char2']['buckets'];
            foreach ($char2Buckets as $char2Bucket) {
                $char2 = $char2Bucket['key'];
                $char1Buckets = $char2Bucket['char1']['buckets'];
                foreach ($char1Buckets as $char1Bucket) {
                    $char1 = $char1Bucket['key'];
                    if ($char1Bucket['total_money']['value'] > ($threshold * self::K_VAN)) {
                        $data[$server][$char2][$char1] = $char1Bucket['total_money']['value'];
                    }
                }
            }
        }
        foreach ($data as $server => $chars2) {
            foreach ($chars2 as $char2 => $char1Arr) {
                $user2 = $this->accountService->getUsernameByChar($server, $char2);
                $users1 = [];
                foreach ($char1Arr as $char1 => $amount) {
                    $user1 = $this->accountService->getUsernameByChar($server, $char1);
                    $user1['amount'] = $amount;
                    $users1[] = $user1;
                }
                $report[$server][] = [
                    'user2'   => $user2,
                    'users1' => $users1,
                ];
            }
        }

        return $report;
    }
}
