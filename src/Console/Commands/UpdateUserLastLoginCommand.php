<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\UserLastLogin;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\KibanaQueryService;

class UpdateUserLastLoginCommand extends Command
{
    const CONDITION_MINIMUM_LEVEL = 50;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:users:update_last_login {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to update users's last login from savehwid Kibana logs";

    /**
     * @var \T2G\Common\Repository\UserRepository
     */
    protected $userRepository;

    /**
     * ImportUserLastLoginCommand constructor.
     *
     * @param \T2G\Common\Repository\UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        parent::__construct();
        $this->userRepository = $userRepository;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $imported  = $notFound = [];
        $processed = 0;
        $size      = 10000;
        $date      = $this->input->getOption('date');
        if (!$date) {
            $date = date('Y-m-d', strtotime("-1 day"));
        }
        try {
            $startDate = new \DateTime($date);
            $endDate   = new \DateTime(date('Y-m-d', $startDate->getTimestamp() + (24 * 3600)));
        } catch (\Exception $e) {
            $this->output->error("Option `date` does not have a valid format `Y-m-d`");
            exit();
        }

        $this->output->text("Importing users last login from `Kibana logs`");
        $this->output->title("Processing " . $startDate->format('Y-m-d'));
        /** @var KibanaQueryService $kibana */
        $kibana  = app(KibanaQueryService::class);
        $results = $kibana->getActiveUsersInAPeriod($startDate, $endDate, $size, $scroll = '5m');
        while ($data = $results->getHits()) {
            $usernames = [];
            // get usernames array to query for users once
            foreach ($data as $log) {
                if (!in_array($log['_source']['user'], $usernames)) {
                    $usernames[] = $log['_source']['user'];
                }
            }
            $users = $this->userRepository->getUsersByNames($usernames);
            $users = array_column($users, 'id', 'name');
            foreach ($data as $log) {
                $username = $log['_source']['user'];
                $userId   = isset($users[$username]) ? $users[$username] : 0;
                if (!$userId) {
                    if (!in_array($username, $notFound)) {
                        $notFound[] = $username;
                    }
                    continue;
                }
                if ($log['_source']['level'] < self::CONDITION_MINIMUM_LEVEL) {
                    continue;
                }
                $timestamp = new \DateTime($log['_source']['@timestamp']);

                $imported[$userId . "-" . $log['_source']['jx_server']] = [
                    'user_id'         => $userId,
                    'last_login_date' => $timestamp,
                    'server'          => $log['_source']['jx_server'],
                ];
            }
            if ($results->getTotalResults() > $size) {
                $results = $kibana->scroll($results->getScrollId());
            } else {
                $results->clear();
            }
        }
        if ($counter = count($notFound)) {
            $this->output->text("There was `{$counter}` users not found in table users.");
            $this->output->listing($notFound);
        }

        $chunks = array_chunk(array_values($imported), 1000);
        foreach ($chunks as $chunk) {
            $chunkCount = count($chunk);
            UserLastLogin::insert($chunk);
            $this->output->text("Imported {$chunkCount} records.");
            $processed += $chunkCount;
        }

        $this->output->success("Imported {$processed} records.");
    }
}
