<?php

namespace T2G\Common\Console\Commands;

use Illuminate\Console\Command;
use T2G\Common\Models\CCU;

class UpdateCCUCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 't2g_common:ccu:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update CCU of game servers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $api = getGameApiClient();
        $ccus = $api->getCCUs();
        $this->output->text("Updating CCU of game servers");
        foreach ($ccus as $server => $ccu) {
            $CCU = new CCU();
            $CCU->server = $server;
            $CCU->online = $ccu;
            $CCU->save();
        }
        $this->output->text("Completed updating CCU of game servers");
    }
}
