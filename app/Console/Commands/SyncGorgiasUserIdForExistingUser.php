<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Services\GorgiasService;
use Illuminate\Console\Command;

class SyncGorgiasUserIdForExistingUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gorgias:sync-existing-agent {agent}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agent = Agent::find($this->argument('agent'));

        if ($agent) {
            (new GorgiasService)->syncGorgiasUserId($agent);
        }
    }
}
