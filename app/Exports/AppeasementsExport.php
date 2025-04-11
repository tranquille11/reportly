<?php

namespace App\Exports;

use App\Models\Appeasement;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AppeasementsExport implements FromQuery, WithHeadings
{
    use Exportable;

    public function __construct(private $filters) {}

    public function query()
    {
        return Appeasement::query()
            ->filter($this->filters)
            ->leftJoin('appeasement_reasons', 'appeasement_reasons.id', '=', 'appeasements.reason_id')
            ->leftJoin('brands', 'brands.id', '=', 'appeasements.brand_id')
            ->latest('date')
            ->select(['order_id', 'order_number', 'amount', 'date', 'note', 'status', 'appeasement_reasons.name', 'brands.shorthand']);
    }

    public function headings(): array
    {
        return [
            'order_id',
            'order_number',
            'amount',
            'date',
            'note',
            'status',
            'reason',
            'brand',
        ];
    }
}
