<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Repository\PaymentRepository;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\GoldWithdrawingService;
use TCG\Voyager\Models\Setting;

class MonitorJXGoldCommand extends Command
{
    const LAST_RUN_SETTING_KEY = 'system.t2g_common:monitor:gold:lastrun';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:monitor:gold';

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
        $this->discord = new DiscordWebHookClient(config('t2g_common.discord.webhooks.police'));
    }

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to monitor JX gold withdrawing from Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lastRunTimestamp = setting(self::LAST_RUN_SETTING_KEY) ?? strtotime('-1 day');
        $startDate = new \DateTime('@' . $lastRunTimestamp);
        $this->output->text("Checking gold withdrawing from `Kibana logs`");
        $this->output->title("Processing from " . $startDate->format('c'));
        $kibana  = app(GoldWithdrawingService::class);
        $results = $kibana->getGoldWithdrawingLogs($startDate);
        $data = $results->getHits();
        foreach ($data as $log) {
            if ($this->checkForWarning($log)) {
                $this->alertMonitor("Giao dịch rút xu khả nghi. S{$log['_source']['jx_server']} `{$log['_source']['message']}`");
                $lastRunTimestamp = strtotime($log['_source']['created_at']);
                continue;
            }
            if ($log['_source']['amount'] >= 500) {
                $type = $log['_source']['field'] == 'ExtPoint3' ? "CK" : "Card";
                $message = sprintf(
                    "User `%s` server S%s (`%s`) rút `%s` Xu - %s - vào lúc %s",
                    $log['_source']['user'],
                    $log['_source']['jx_server'],
                    $log['_source']['char'],
                    $log['_source']['amount'],
                    $type,
                    $log['_source']['created_at']
                );

                $this->warningMonitor($message);
            }
            $lastRunTimestamp = strtotime($log['_source']['created_at']);
        }
        if (!$data) {
            $lastRunTimestamp = time();
        }
        $this->saveLastRunSetting($lastRunTimestamp + 1);

        $this->output->success("Done processing.");
    }

    private function alertMonitor(string $message)
    {
        $this->warn($message);
        $this->discord->sendWithEmbed("Ò Í E! Ò E!", $message, DiscordWebHookClient::EMBED_COLOR_ALERT);
        sleep(1);
    }

    private function warningMonitor(string $message)
    {
        $this->discord->sendWithEmbed("Giao dịch rút xu", $message, DiscordWebHookClient::EMBED_COLOR_NOTICE);
        sleep(1);
    }

    /**
     * @param $log
     *
     * @return bool
     */
    private function checkForWarning($log)
    {
        if ($log['_source']['sign'] != '-') {
            return true;
        }
        $repo = app(PaymentRepository::class);
        $checkPaid = $repo->isUserPaid($log['_source']['user']);
        if (!$checkPaid) {
            return true;
        }

        return false;
    }

    private function saveLastRunSetting($lastRunTimestamp)
    {
        $lastRunSetting = Setting::where('key', self::LAST_RUN_SETTING_KEY)->first();
        if (!$lastRunSetting) {
            $lastRunSetting = new Setting();
            $lastRunSetting->key = self::LAST_RUN_SETTING_KEY;
            $lastRunSetting->display_name = self::LAST_RUN_SETTING_KEY;
            $lastRunSetting->type = 'number';
            $lastRunSetting->group = 'System';
        }
        $lastRunSetting->value = $lastRunTimestamp;
        $lastRunSetting->save();
    }
}
