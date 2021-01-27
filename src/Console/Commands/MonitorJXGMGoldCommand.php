<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\GoldWithdrawingService;
use TCG\Voyager\Models\Setting;

class MonitorJXGMGoldCommand extends AbstractJXCommand
{
    const LAST_RUN_SETTING_KEY = 'system.t2g_common:monitor:gold_gm:lastrun';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:gold_gm';

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
        $webHookConfigs = config('t2g_common.discord.webhooks');
        $webhookUrl = $webHookConfigs['monitor_gold_gm'] ?: $webHookConfigs['police'];
        $this->discord = new DiscordWebHookClient($webhookUrl);
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor JX GM gold withdrawing command from Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastRunTimestamp = setting(self::LAST_RUN_SETTING_KEY) ?? strtotime('-1 days');
        $startDate = new \DateTime('@' . $lastRunTimestamp);
        $this->output->text("Checking GM gold withdrawing from `Kibana logs`");
        $this->output->title("Processing from " . $startDate->format('c'));
        $kibana  = app(GoldWithdrawingService::class);
        $results = $kibana->getGMGoldWithdrawingLogs($startDate);
        $data = $results->getHits();
        foreach ($data as $log) {
            if (in_array('_grokparsefailure', $log['_source']['tags'])) {
                continue;
            }
            $message = sprintf(
                "GM `%s` server S%s (`%s`) rút `%s` Xu vào lúc %s",
                $log['_source']['user'],
                $log['_source']['jx_server'],
                $log['_source']['char'],
                $log['_source']['amount'],
                $log['_source']['created_at']
            );

            $this->warningMonitor($message, $log['_source']['amount']);
            $lastRunTimestamp = strtotime($log['_source']['created_at']);
        }
        if (!$data) {
            $lastRunTimestamp = time();
        }
        $this->saveLastRunSetting(self::LAST_RUN_SETTING_KEY, $lastRunTimestamp + 1);

        $this->output->success("Done processing.");
    }

    private function warningMonitor(string $message, $amount)
    {
        $color = $amount > 1000 ? DiscordWebHookClient::EMBED_COLOR_ALERT : DiscordWebHookClient::EMBED_COLOR_NOTICE;
        $this->discord->sendWithEmbed("GM Rút Xu", $message, $color);
        sleep(1);
    }

}
