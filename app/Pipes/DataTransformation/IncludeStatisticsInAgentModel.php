<?php

namespace App\Pipes\DataTransformation;

class IncludeStatisticsInAgentModel
{
    public function __construct() {}

    public function __invoke($agents, $next)
    {

        $agents->map(function ($agent) {
            foreach ($agent->statistics as $statistic) {
                $statisticName = str_replace('-', '_', $statistic->statistic);
                $agent->{$statisticName} = $statistic->number;
            }

            return $agent;
        });

        return $next($agents);
    }
}
