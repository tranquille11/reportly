<?php

namespace App\Exports;

use App\Exports\Sheets\BestSellerSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class BestSellersExport implements WithMultipleSheets
{
    use Exportable;

    private array $data;

    private array $categories;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        foreach ($this->data as $name => $value) {
            $sheets[] = new BestSellerSheet($value, $name);
        }

        return $sheets;
    }
}
