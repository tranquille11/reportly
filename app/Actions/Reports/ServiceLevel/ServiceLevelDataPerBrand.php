<?php

namespace App\Actions\Reports\ServiceLevel;

use App\Models\Brand;
use Illuminate\Support\Collection;

class ServiceLevelDataPerBrand
{
    public function handle(string $start, string $end, int $threshold): Collection
    {
        return Brand::withCount([
            'callsInboundOrMissed' => fn ($q) => $q->betweenDates($start, $end),
            'callsOverThreshold' => fn ($q) => $q->betweenDates($start, $end)->waitTimeOver($threshold),
            'callsUnderThreshold' => fn ($q) => $q->betweenDates($start, $end)->waitTimeUnder($threshold),
        ])
            ->get()
            ->map(function ($brand) {
                return [
                    'brand' => $brand->name,
                    'total_calls' => $brand->calls_inbound_or_missed_count ?? 0,
                    'score' => $brand->calls_under_threshold_count * 100,
                    'service_level' => $brand->calls_under_threshold_count > 0 && $brand->calls_inbound_or_missed_count > 0
                        ? round(($brand->calls_under_threshold_count * 100) / $brand->calls_inbound_or_missed_count, 1).'%'
                        : '0%',
                ];
            })
            ->sortByDesc('total_calls');

    }
}
