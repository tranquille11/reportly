<?php

namespace App\Jobs;

use App\Models\Agent;
use App\Services\GorgiasService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;

class RetrieveGorgiasStatisticsPerAgent implements ShouldQueue
{
    use Queueable;

    public $tries = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(protected Agent $agent, protected string $start, protected string $end) {}

    /**
     * Execute the job.
     */
    public function handle(GorgiasService $gorgiasService): void
    {

        sleep(5);

        $start = Carbon::parse($this->start);
        $end = Carbon::parse($this->end);

        $allowedStatistics = config('gorgias.allowed_statistics');

        $missingStats = [];

        foreach ($allowedStatistics as $stat) {
            if (! $this->agent->statistics()->where('statistic', $stat)->where('start_date', $start->format('Y-m-d'))->where('end_date', $end->format('Y-m-d'))->exists()) {
                $missingStats[] = $stat;
            }
        }

        if (! $missingStats) {
            return;
        }

        $statistics = $gorgiasService->fetchAgentsOverviewStatistics(
            agent: $this->agent,
            start: $start->toAtomString(),
            end: $end->toAtomString(),
        );

        $data = [];

        foreach ($statistics as $name => $statistic) {

            $value = in_array($name, ['rating-chat', 'rating-email'])
                ? $statistic->json()['data']['data'][2]['value']
                : $statistic->json()['data']['data']['value'];

            if ($this->agent->statistics()->where('statistic', $name)->where('start_date', $start->format('Y-m-d'))->where('end_date', $end->format('Y-m-d'))->exists()) {
                continue;

            }

            $data[] = [
                'statistic' => $name,
                'start_date' => $start->format('Y-m-d'),
                'end_date' => $end->format('Y-m-d'),
                'number' => $value,
            ];
        }

        if ($data) {
            $this->agent->statistics()->createMany($data);
        }

    }
}
