<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Models\AggregateStatistic;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ShiftsDataAggregation implements ShouldQueue
{
    use Queueable;

    private $shifts;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $startDate, private string $endDate)
    {
        $this->shifts = AggregateStatistic::query()
            ->where('statistic', 'shifts_worked')
            ->where('start_date', $this->startDate)
            ->get();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $agents = Agent::with(['shifts' => fn ($q) => $q->where('start_date', $this->startDate)])->representative()->get();

        foreach ($agents as $agent) {
            foreach ($agent->shifts as $shift) {
                if ($this->shifts->where('agent_id', $agent->id)->isEmpty()) {
                    AggregateStatistic::create([
                        'agent_id' => $agent->id,
                        'statistic' => 'shifts_worked',
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate,
                        'number' => $shift->number,
                    ]);
                }
            }
        }
    }
}
