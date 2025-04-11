<?php

namespace App\Actions\Reports\AgentData;

use App\Models\Agent;

class DataPerAgent
{
    public function handle(array $filters, string $startDate, string $endDate, array $agentRoles = [])
    {
        return Agent::select(['id', 'name'])
            ->filter($filters)
            ->agentData($startDate, $endDate)
            ->when($agentRoles, fn ($q) => $q->whereIn('role', $agentRoles))
            ->get();
    }
}
