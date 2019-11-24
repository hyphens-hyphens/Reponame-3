<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\UserLastLogin;
use T2G\Common\Repository\UserRepository;
use T2G\Common\Services\JXApiClient;

class UpdateUserLastLoginCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:users:update_last_login {--date=} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Command to update users's last login";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $processed = 0;
        $date = $this->input->getOption('date');
        try {
            $date = new \DateTime($date);
        } catch (\Exception $e) {
            $this->output->error("Option `date` does not have a valid format `Y-m-d`");
            exit();
        }

        $this->output->text("Updating users last login");
        $jxApi = app(JXApiClient::class);
        try {
            $usersLastLogin = $jxApi->getUsersLastLogin($date);
        } catch (\Exception $e) {
            $this->output->error("Cannot get users last login data. Exit!");
            exit();
        }
        $usernames = [];
        foreach ($usersLastLogin as $item) {
            $usernames[] = $item['cAccName'];
        }
        /** @var UserRepository $userRepository */
        $userRepository = app(UserRepository::class);
        $users = $userRepository->getUsersByNames($usernames);
        $users = array_column($users, 'id', 'name');
        foreach ($usersLastLogin as $item) {
            if (!isset($users[strtolower($item['cAccName'])])) {
                continue;
            }
            $record = new UserLastLogin();
            $record->user_id = $users[strtolower($item['cAccName'])];
            $record->last_login_date = $item['dLoginDate']['date'];
            $record->last_logout_date = $item['dLogoutDate']['date'];
            $record->save();
            $this->output->success("Updated user `{$record->user_id}`");
            $processed++;
        }

        $this->output->success("Processed {$processed} rows.");
    }
}
