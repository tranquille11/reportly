<?php

namespace App\Exports;

use App\Exports\Sheets\AgentData\GeneralSheet;
use App\Exports\Sheets\AgentData\RecordingsSheet;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AgentDataExport implements WithMultipleSheets
{
    public function __construct(public Carbon $start, public Carbon $end, public array $roles) {}

    public function sheets(): array
    {
        return [
            new GeneralSheet(title: 'General', start: $this->start, end: $this->end, roles: $this->roles),
            new RecordingsSheet(title: 'Hung up in 30s', start: $this->start, end: $this->end, roles: $this->roles, callType: 'callsHungUpUnder30Seconds'),
            new RecordingsSheet(title: 'Hold time > 10min', start: $this->start, end: $this->end, roles: $this->roles, callType: 'callsWithHighHoldTime'),
            new RecordingsSheet(title: 'Talk time > 15min', start: $this->start, end: $this->end, roles: $this->roles, callType: 'callsWithHighTalkTime'),
        ];
    }
}
