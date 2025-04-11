<?php

namespace App\Observers;

use App\Models\Agent;
use Illuminate\Support\Facades\Artisan;

class AgentObserver
{
    /**
     * Handle the Agent "created" event.
     */
    public function created(Agent $agent): void {}

    public function updating(Agent $agent): void
    {
        if ($agent->getOriginal('stage_name') === $agent->stage_name) {
            return;
        }

        Artisan::queue('gorgias:sync-existing-agent', ['agent' => $agent->id]);
    }

    /**
     * Handle the Agent "updated" event.
     */
    public function updated(Agent $agent): void
    {
        //
    }

    /**
     * Handle the Agent "deleted" event.
     */
    public function deleted(Agent $agent): void
    {
        //
    }

    /**
     * Handle the Agent "restored" event.
     */
    public function restored(Agent $agent): void
    {
        //
    }

    /**
     * Handle the Agent "force deleted" event.
     */
    public function forceDeleted(Agent $agent): void
    {
        //
    }
}
