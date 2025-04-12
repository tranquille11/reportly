<?php

namespace App\Actions\Reports\Appeasements;

use App\Models\Appeasement;
use Illuminate\Support\Carbon;

class AppeasementsPerMonth
{
    public function handle(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $rows = [];
        $reasons = [];
        $dates = [];

        $appeasements = Appeasement::selectRaw("strftime('%m/%Y', date) as month, COUNT(*) as count, brands.name as brand, appeasement_reasons.name as reason, SUM(amount) as total")
            ->groupByRaw('brand, month, reason')
            ->whereBetween('date', [$start, $end])
            ->join('brands', 'brands.id', '=', 'appeasements.brand_id')
            ->join('appeasement_reasons', 'appeasement_reasons.id', '=', 'appeasements.reason_id')
            ->get()
            ->groupBy('brand')
            ->each(function ($item, $brand) use (&$reasons, &$dates) {
                $currentReasons = $item->pluck('reason')->unique()->sort()->values()->toArray();
                $reasons[$brand] = $currentReasons;
                $dates['formatted'] = $item->pluck('month')->unique()->map(function ($month) use (&$dates) {
                    $split = explode('/', $month);

                    return Carbon::createFromDate($split[1], $split[0], 1)->format('F Y');
                })->sort(fn ($a, $b) => strtotime($a) - strtotime($b))->toArray();

                $dates['original'] = $item->pluck('month')->unique()->toArray();

            })
            ->map(function ($brand) {
                return $brand->sortBy('reason')->groupBy('month');
            })
            ->map(function ($item, $brand) use ($reasons, $dates) {
                $missingMonths = collect($dates['original'])->diff($item->keys()->toArray());

                foreach ($missingMonths as $m) {

                    $monthData = [];

                    foreach ($reasons[$brand] as $r) {
                        $monthData[] = [
                            'reason' => $r,
                            'count' => 0,
                        ];
                    }

                    $item->put($m, collect($monthData));

                }

                return $item->map(function ($month, $key) use ($reasons, $brand, &$item) {
                    $missingReasons = collect($reasons[$brand])->diff($month->pluck('reason')->toArray());

                    foreach ($missingReasons as $reason) {
                        $month[] = [
                            'reason' => $reason,
                            'count' => 0,
                        ];
                    }

                    return $month->sortBy('reason')->values()->map(function ($entry) {
                        return [
                            'count' => $entry['count'],
                        ];
                    });
                })->sortKeysUsing(function ($a, $b) {
                    $a = explode('/', $a);
                    $b = explode('/', $b);

                    return Carbon::createFromDate($a[1], $a[0], 1)->timestamp - Carbon::createFromDate($b[1], $b[0], 1)->timestamp;
                });
            })
            ->sortKeysDesc()
            ->each(function ($item, $brand) use (&$rows, $reasons) {
                foreach ($item as $month) {
                    foreach ($month as $key => $entry) {

                        if (! isset($rows[$brand][$key])) {
                            $rows[$brand][$key][] = $reasons[$brand][$key];
                        }

                        $rows[$brand][$key][] = $entry['count'];
                    }
                }
            })
            ->toArray();

        return ['data' => $rows, 'dates' => $dates['formatted']];

    }
}
