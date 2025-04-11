<?php

namespace App\Exports;

use App\Models\Agent;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AgentsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function query()
    {
        return Agent::query()->select([
            'id',
            'name',
            'stage_name',
            'email',
            'role',
            'settings',
        ]);
    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'stage_name',
            'email',
            'role',
            'settings',
        ];
    }
}
