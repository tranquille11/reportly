<?php

namespace App\Actions\Reports\Appeasements;

use App\Models\Appeasement;
use App\Models\Brand;
use Illuminate\Support\Carbon;

class NeverShippedData
{
    public function handle(string $startDate, string $endDate, ?string $brand)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $brand = Brand::firstWhere('name', $brand);

        $rows = [];

        $appeasements = Appeasement::whereHas('reason', fn ($q) => $q->where('shorthand', 'NS'))
            ->whereHas('location', fn ($q) => $q->where('type', 'warehouse'))
            ->when($brand, fn ($query) => $query->where('brand_id', $brand->id))
            ->select(['order_number', 'date', 'location_id', 'reason_id', 'amount'])
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy('location.number')
            ->sortByDesc(fn ($appeasements) => count($appeasements))
            ->each(function ($appeasements, $location) use (&$rows) {
                $rows['Never Shipped'][] = [
                    'Store #' => $location,
                    '# of refunds' => $appeasements->count(),
                    'Amount' => $appeasements->sum('amount') / 100,
                ];
                $orders = $appeasements->sortByDesc(fn ($items) => $items->count())
                    ->pluck('order_number')
                    ->prepend($location)
                    ->toArray();

                $rows['Orders'][] = $orders;
            });

        if ($rows) {
            $rows['Orders'] = array_map(null, ...array_values($rows['Orders']));

            if (count($rows['Never Shipped']) === 1) {
                foreach ($rows['Orders'] as &$row) {
                    $row = [$row];
                }
            }
        }

        return $rows;
    }
}
