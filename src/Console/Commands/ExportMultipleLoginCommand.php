<?php

namespace T2G\Common\Console\Commands;

use T2G\Common\Models\ElasticSearch\SearchResult;
use T2G\Common\Services\DiscordWebHookClient;
use T2G\Common\Services\Kibana\AbstractKibanaService;
use T2G\Common\Services\Kibana\MultipleLoginDetectionService;
use T2G\Common\Util\CommonHelper;

class ExportMultipleLoginCommand extends AbstractJXCommand
{
    const MAX_ACCOUNT_PER_PC = 4;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:export:multiple_login {days=5}';

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
    protected $description = "Command to export multiple login on 1 PC based on Kibana logs.";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $multipleLoginDetectionService = app(MultipleLoginDetectionService::class);
        $days = $this->input->getArgument('days');
        for($i = 0; $i < $days; $i++) {
            $from = new \DateTime(date('Y-m-d 00:00:00', strtotime("-{$i} days")));
            $date = $from->format('Y-m-d');
            $file = storage_path("app/console_export/{$date}.txt");
            $interval = \DateInterval::createFromDateString("1 day");
            $results = $multipleLoginDetectionService->getMultipleLoginLogs($from, $interval, AbstractKibanaService::MAX_RESULTS_WINDOW);

            $this->doExport($file, $results);
        }
    }

    private function formatReportMessage($server, $logFile, $hwid, array $userArray)
    {
        $file = explode('/', $logFile);
        $file = last($file);
        $template = <<<'TEMPLATE'
======================
Server: S%s
File: %s
HWID: %s
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
                $listUsers .= sprintf(
                    "- %s, %s (%s), lv %s, %s\n",
                    $user['hwid'],
                    $user['user'],
                    $user['char'],
                    $user['level'],
                    $user['map']
                );
                $existed[] = $user['user'];
            }
        }
        return sprintf($template, $server, $file, $hwid, $listUsers);
    }

    /**
     * @param                                               $file
     * @param \T2G\Common\Models\ElasticSearch\SearchResult $results
     */
    private function doExport($file, SearchResult $results)
    {
        $report = [];
        foreach ($results->getHits() as $hit) {
            $row = $hit['_source'];
            if (empty($row['hwid']) || in_array($row['user'], $this->excluded)) {
                continue;
            }

            $filteredHwid = CommonHelper::getFilteredHwid($row['hwid']);
            $report[$row['jx_server'] . "|" . $row['log']['file']['path']][$filteredHwid][$row['user']][] = $row;
        }
        $messages = [];
        foreach ($report as $serverAndLogFile => $hwidArray) {
            $serverAndLogFileSplitted = explode('|', $serverAndLogFile);
            $server = $serverAndLogFileSplitted[0];
            $logFile = $serverAndLogFileSplitted[1];
            foreach ($hwidArray as $hwid => $userArray) {
                if (count($userArray) <= self::MAX_ACCOUNT_PER_PC ) {
                    continue;
                }
                $messages[] = $this->formatReportMessage($server, $logFile, $hwid, $userArray);
            }
        }
        file_put_contents($file, implode("\n", $messages));
        $this->output->text("Exported {$file}");
    }
}
