<?php

namespace App\Actions\Reports\ServiceLevel;

use Illuminate\Support\Collection;

class OverallServiceLevelData
{
    public function handle(Collection $data): array
    {
        $calls = $data->sum('total_calls');

        return [
            'Total calls' => $calls,
            'Overall service level' => $data->sum('score') ? round($data->sum('score') / $calls, 1).'%' : 0 .'%',
            'Total brands' => $data->count(),
        ];
    }
}
