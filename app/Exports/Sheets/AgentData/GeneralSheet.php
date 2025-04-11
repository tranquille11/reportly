<?php

namespace App\Exports\Sheets\AgentData;

use App\Models\Agent;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class GeneralSheet implements FromQuery, WithHeadings, WithMapping, WithStrictNullComparison, WithTitle
{
    public function __construct(public string $title, public Carbon $start, public Carbon $end, public array $roles) {}

    public function query()
    {
        return Agent::query()
            ->agentData($this->start, $this->end)
            ->when($this->roles, fn ($q) => $q->whereIn('role', $this->roles))
            ->orderBy('name');
    }

    public function map($agent): array
    {
        return [
            $agent->name,
            $agent->inbound_calls_count,
            $agent->calls_without_disposition_count,
            $agent->calls_hung_up_under30_seconds_count,
            $agent->calls_outbound_missed_count,
            $agent->calls_with_high_hold_time_count,
            $agent->calls_with_high_talk_time_count,
        ];
    }

    public function headings(): array
    {
        return [
            'Agent name',
            'Total calls',
            'No disposition',
            'Hung up in 30 sec',
            'Outbound missed',
            'Hold > 10 min',
            'Talktime > 15 min',
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
