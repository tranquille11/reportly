<?php

namespace App\Jobs;

use App\Actions\Reports\AgentData\DataPerAgent;
use App\Models\AggregateStatistic;
use App\Models\Call;
use App\Models\Setting;
use App\Models\User;
use App\Notifications\CallsMissingFromDatabase;
use Carbon\CarbonImmutable;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

class TalkdeskDataAggregation implements ShouldQueue
{
    use Queueable;

    private array $statisticsDates;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->statisticsDates = AggregateStatistic::query()
            ->where('statistic', 'total-calls')
            ->select('start_date')
            ->distinct()
            ->get()
            ->pluck('start_date')
            ->toArray();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $holidays = Setting::where('key', 'holidays')->first()->value;
        $holidays = collect($holidays)->pluck('date')->toArray();

        $firstCallInDatabase = Call::orderBy('start_time')->first()?->start_time;

        if (! $firstCallInDatabase) {
            return;
        }

        $firstCallDate = CarbonImmutable::parse($firstCallInDatabase)->subMonth()->startOfMonth()->format('Y-m-d');

        $monthsPeriod = CarbonPeriod::create($firstCallDate, '1 month', now());
        $monthsBetween = collect([]);

        foreach ($monthsPeriod as $date) {
            $monthsBetween->push($date->format('Y-m-d'));
        }

        $monthsNotInDatabase = $monthsBetween->tap(function ($collection) {
            $collection->shift();
            $collection->pop();
        })->toArray();

        if (! $monthsNotInDatabase) {
            return;
        }

        $missingCallDates = [];

        foreach ($monthsNotInDatabase as $month) {

            $month = CarbonImmutable::parse($month);
            $firstOfMonth = $month->startOfMonth()->format('Y-m-d');
            $lastOfMonth = $month->endOfMonth()->format('Y-m-d');

            if (in_array($firstOfMonth, $this->statisticsDates)) {
                continue;
            }

            $allMonthDates = $this->createPeriod($firstOfMonth, $lastOfMonth, $holidays);

            $monthMissingCallsDates = [];

            foreach ($allMonthDates as $date) {

                $callsExist = Call::whereDate('start_time', $date)->exists();

                if (! $callsExist) {
                    $monthMissingCallsDates[] = $date;
                }
            }

            if (! empty($monthMissingCallsDates)) {
                $missingCallDates = array_merge($missingCallDates, $monthMissingCallsDates);

                continue;
            }

            // Insert here aggregate calls statistics for each agent
            $data = [];
            $agents = app(DataPerAgent::class)->handle([], $firstOfMonth, $lastOfMonth);

            foreach ($agents as $agent) {
                $data[] = [
                    'statistic' => 'total-calls',
                    'number' => $agent->inbound_calls_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];

                $data[] = [
                    'statistic' => 'hung-up-under-threshold',
                    'number' => $agent->calls_hung_up_under30_seconds_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];

                $data[] = [
                    'statistic' => 'outbound-missed',
                    'number' => $agent->calls_outbound_missed_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];

                $data[] = [
                    'statistic' => 'high-hold-time',
                    'number' => $agent->calls_with_high_hold_time_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];

                $data[] = [
                    'statistic' => 'high-talk-time',
                    'number' => $agent->calls_with_high_talk_time_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];

                $data[] = [
                    'statistic' => 'no-disposition',
                    'number' => $agent->calls_without_disposition_count,
                    'start_date' => $firstOfMonth,
                    'end_date' => $lastOfMonth,
                    'agent_id' => $agent->id,
                ];
            }

            AggregateStatistic::insert($data);

            if (! AggregateStatistic::where('statistic', 'closed-emails')->where('start_date', $firstOfMonth)->exists()) {
                Artisan::queue('gorgias:statistics', [
                    'start' => $firstOfMonth,
                    'end' => $lastOfMonth,
                ]);
            }
        }

        if ($missingCallDates) {

            $users = User::all();
            Notification::send($users, new CallsMissingFromDatabase($missingCallDates));

            return;
        }

    }

    protected function createPeriod(string $firstDay, string $lastDay, array $holidays)
    {
        $period = CarbonPeriod::create($firstDay, $lastDay);

        $datesBetween = [];

        foreach ($period as $date) {
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }

            $datesBetween[] = $date->format('Y-m-d');
        }

        return $datesBetween;
    }
}
