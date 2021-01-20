<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\JXShopService;
use TCG\Voyager\Models\Setting;

class MonitorJXShopCommand extends AbstractJXCommand
{
    const LAST_RUN_SETTING_KEY = 'system.t2g_common:monitor:shop:lastrun';
    const GOLD_ITEM_NAME = 'TiÒn ®ång';
    const COST_THRESHOLD = 100;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:shop';

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
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.monitor_shop'));
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor JX Shops from Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startDate = new \DateTime('@' . strtotime('-5 minutes'));
        $this->output->text("Checking shop transaction from `Kibana logs`");
        $this->output->title("Processing from " . $startDate->format('c'));
        $kibana  = app(JXShopService::class);
        $results = $kibana->getShopLogs($startDate);
        $data = $results->getHits();
        $goldTransfers = $itemTransfers = [];
        foreach ($data as $log) {
            if ($this->isGoldTransfered($log)) {
                $goldTransfers[$log['_source']['server_name']][] = $log['_source'];
                continue;
            }
            if ($log['_source']['cost'] <= self::COST_THRESHOLD) {
                $itemTransfers[$log['_source']['server_name']][] = $log['_source'];
                continue;
            }
        }
        foreach ($goldTransfers as $server => $logs) {
            $this->alertGoldTransfered($server, $logs);
        }
        foreach ($itemTransfers as $server => $logs) {
            $this->alertItemTransfered($server, $logs);
        }

        $this->output->success("Done processing.");
    }

    private function alertGoldTransfered($server, $logs)
    {
        $prefix = "Server: {$server}\r\n";
        $chunks = array_chunk($logs, 15);
        foreach ($chunks as $logs) {
            $messages = [];
            foreach ($logs as $log) {
                $cost = number_format($log['cost']);
                $messages[] = "- {$log['user']} <{$log['char']}> mua Xu giá {$cost} lượng từ `{$log['user2']} <{$log['char2']}>`";
            }
            if ($messages) {
                $message = $prefix . implode("\r\n", $messages);
                $this->discord->sendWithEmbed(
                    "Mua Xu từ Shop bày bán",
                    $message,
                    DiscordWebHookClient::EMBED_COLOR_ALERT
                );
                $this->output->text($message);
                sleep(1);
            }
        }
    }

    private function alertItemTransfered($server, $logs)
    {
        $prefix = "Server: {$server}\r\n";
        $costThreshold = self::COST_THRESHOLD;
        $chunks = array_chunk($logs, 15);
        foreach ($chunks as $logs) {
            $messages = [];
            foreach ($logs as $log) {
                $cost = number_format($log['cost']);
                $messages[] = "- {$log['user']} <{$log['char']}> mua {$log['item']} giá {$cost} lượng từ `{$log['user2']} <{$log['char2']}>`";
            }
            if ($messages) {
                $message = $prefix . implode("\r\n", $messages);
                $this->discord->sendWithEmbed(
                    "Mua Item giá <= {$costThreshold} lượng từ Shop bày bán",
                    $message,
                    DiscordWebHookClient::EMBED_COLOR_NOTICE
                );
                $this->output->text($message);
                sleep(1);
            }
        }
    }

    /**
     * @param $log
     *
     * @return bool
     */
    private function isGoldTransfered($log)
    {
        return !empty($log['_source']['item']) && $log['_source']['item'] == self::GOLD_ITEM_NAME;
    }

}
