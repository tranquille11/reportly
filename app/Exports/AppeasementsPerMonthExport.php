<?php

namespace App\Exports;

use App\Exports\Sheets\AppeasementsPerMonthSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AppeasementsPerMonthExport implements WithMultipleSheets
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data['data'] as $brand => $data) {
            $sheets[] = new AppeasementsPerMonthSheet(
                data: $data,
                name: $brand,
                headings: array_merge(['Appeasement reason'], $this->data['dates'])
            );
        }

        return $sheets;
    }
}
