<?php

namespace App\Actions\Reports\Appeasements;

use App\Models\Appeasement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReturnsAndUnlocksData
{
    public function handle(string $startDate, string $endDate)
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        $rows = [];

        Appeasement::whereHas('reason', function ($q) {
            return $q->reasonsForInventoryControl();
        })->with(['reason', 'location'])
            ->select(['order_number', 'date', 'products', 'location_id', 'reason_id'])
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->orderByDesc('date')
            ->get()
            ->each(function (Appeasement $appeasement) use (&$rows) {

                if (! $appeasement->products) {
                    return true;
                }
                foreach ($appeasement->products as $product) {

                    $rows[Str::plural($appeasement->reason->name)][] = [
                        'order_number' => $appeasement->order_number,
                        'store' => $appeasement->location->name,
                        'product' => $product,
                        'date' => $appeasement->date,
                    ];
                }
            });

        return $rows;

    }
}
