<?php

namespace App\Actions\Reports\ServiceLevel;

use Illuminate\Support\Collection;

class PrepareServiceLevelDataForExport
{
    public function handle(Collection $data): array
    {
        $totalCalls = $data->sum('total_calls');
        $score = $data->sum('score');

        return [
            $data->pluck('brand')->prepend('Overall')->toArray(),
            $data->pluck('total_calls')->prepend($totalCalls)->toArray(),
            $totalCalls === 0
                ? $data->pluck('service_level')->prepend('0%')->toArray()
                : $data->pluck('service_level')->prepend(round($score / $totalCalls, 1).'%')->toArray(),
        ];

    }
}
