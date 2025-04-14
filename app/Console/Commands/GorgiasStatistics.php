<?php

namespace App\Console\Commands;

use App\Jobs\RetrieveGorgiasStatisticsPerAgent;
use App\Models\Agent;
use App\Models\User;
use App\Notifications\AgentsNotInGorgiasNotification;
use App\Services\GorgiasService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

class GorgiasStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gorgias:statistics {start?} {end?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves statistics from Gorgias from the specified date range.';

    /**
     * Execute the console command.
     */
    public function handle(GorgiasService $gorgiasService)
    {

        $agents = Agent::representative()->get();
        $gorgiasAgents = $gorgiasService->fetchUsers();

        $start = $this->argument('start');
        $end = $this->argument('end');

        // If no start or end date is provided, use the last start/end of the last month by default.
        if (! $start || ! $end) {
            $start = Carbon::now()->startOfMonth()->subMonth()->format('Y-m-d');
            $end = Carbon::now()->subMonth()->endOfMonth()->format('Y-m-d');
        }

        $agentsNotInGorgias = [];

        foreach ($agents as $agent) {
            $currentGorgiasAgent = $gorgiasAgents->firstWhere('name', $agent->stage_name);

            if (! $currentGorgiasAgent) {
                $agent->update(['settings->gorgias_user_id' => null]);
                $agentsNotInGorgias[] = $agent;

                continue;
            }

            RetrieveGorgiasStatisticsPerAgent::dispatch($agent, $start, $end);
        }

        if ($agentsNotInGorgias) {
            $users = User::all();
            Notification::send($users, new AgentsNotInGorgiasNotification($agentsNotInGorgias));
        }
    }
}
