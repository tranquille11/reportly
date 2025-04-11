<?php

namespace App\Exports\Sheets\AgentData;

use App\Models\Agent;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

class RecordingsSheet implements FromArray, WithTitle
{
    public function __construct(
        public string $title,
        public Carbon $start,
        public Carbon $end,
        public array $roles,
        public string $callType
    ) {}

    public function array(): array
    {
        $agents = Agent::with([$this->callType => fn ($q) => $q->whereBetween('start_time', [$this->start, $this->end])])
            ->when($this->roles, fn ($q) => $q->whereIn('role', $this->roles))
            ->get()
            ->reject(fn ($agent) => $agent->{$this->callType}->count() == 0)
            ->sortByDesc($this->callType)
            ->map(fn ($agent) => $agent->{$this->callType}->pluck('recording')->prepend($agent->name)->toArray())
            ->toArray();

        return array_map(null, ...array_values($agents));
    }

    public function title(): string
    {
        return $this->title;
    }
}
