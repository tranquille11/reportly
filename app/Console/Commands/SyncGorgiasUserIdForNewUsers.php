<?php

namespace App\Console\Commands;

use App\Models\Agent;
use App\Services\GorgiasService;
use Illuminate\Console\Command;

class SyncGorgiasUserIdForNewUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gorgias:sync-user-ids';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync Gorgias User IDs for newly created users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $agents = Agent::where('settings->gorgias_user_id', null)->get();

        if (! $agents) {
            return 1;
        }

        $service = new GorgiasService;
        $gorgiasUsers = $service->fetchUsers();

        foreach ($agents as $agent) {
            $currentAgent = $gorgiasUsers->firstWhere('name', $agent->stage_name);

            if (! $currentAgent) {
                continue;
            }

            $agent->update(['settings->gorgias_user_id' => $currentAgent['id']]);
        }
    }
}
