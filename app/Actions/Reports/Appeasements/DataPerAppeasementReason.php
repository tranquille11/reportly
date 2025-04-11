<?php

namespace App\Actions\Reports\Appeasements;

use App\Models\Appeasement;
use App\Models\Brand;
use Illuminate\Support\Carbon;

class DataPerAppeasementReason
{
    public function handle(string $startDate, string $endDate, ?string $brand)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $rows = [];

        $brand = $brand ? Brand::firstWhere('name', $brand) : null;

        $appeasements = Appeasement::with(['reason', 'location'])
            ->select(['amount', 'date', 'products', 'brand_id', 'location_id', 'reason_id'])
            ->when($brand, fn ($query) => $query->where('brand_id', $brand->id))
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy('reason.name')
            ->sortByDesc(fn ($appeasements) => count($appeasements))
            ->each(function ($appeasements, $reason) use (&$rows) {
                $rows['GENERAL'][$reason] = [
                    'Appeasement reason' => $reason,
                    '# of refunds' => $appeasements->count(),
                    'Amount' => $appeasements->sum('amount') / 100,
                ];

                if (in_array($reason, ["Manufacturer's defect", 'PreOrder delay'])) {
                    $rows[$reason] = $appeasements->select(['products', 'amount'])->map(function ($appeasement) {

                        if (! isset($appeasement['products'])) {
                            return [
                                'products' => 'No Product',
                                'amount' => $appeasement['amount'],
                            ];
                        }
                        $appeasement['products'] = $appeasement['products'][0] ?? null;

                        return $appeasement;
                    })
                        ->groupBy('products')
                        ->map(function ($group) {

                            return [
                                'products' => $group[0]['products'],
                                '# of refunds' => $group->count(),
                                'Amount' => $group->sum('amount') / 100,
                            ];

                        })
                        ->sortByDesc('# of refunds')
                        ->toArray();
                }

                if (in_array($reason, ['Never Shipped', 'Incomplete shipment', 'Unlocks'])) {
                    $rows[$reason] = $appeasements->groupBy('location.number')
                        ->map(function ($group, $location) {
                            return [
                                'Store #' => $location,
                                '# of refunds' => $group->count(),
                                'Amount' => $group->sum('amount') / 100,
                            ];
                        })
                        ->sortByDesc('# of refunds')
                        ->toArray();
                }

            });

        return $rows;
    }
}
