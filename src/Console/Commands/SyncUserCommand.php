<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Services\JXApiClient;

class SyncUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:sync:user {username=""} {--date=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $username = $this->input->getArgument('username');
        /** @var \Eloquent $userModel */
        $userModel = app(config('t2g_common.models.user_model_class'));
        if (!$username) {
            $from = $this->input->getOption('date');
            $query = $userModel->where('updated_at', '>', $from);
        } else {
            $query = $userModel->where('name', $username);
        }
        $users = $query->get();

        $jx = app(JXApiClient::class);
        /** @var \T2G\Common\Models\AbstractUser $user */
        foreach ($users as $user) {
            $set = $jx->setPassword($user->name, $user->getRawPassword());
            if (!$set) {
                $jx->createUser($user->name, $user->getRawPassword());
            }
            $this->output->text("Synced successfully. " . $user->name);
        }
    }
}
