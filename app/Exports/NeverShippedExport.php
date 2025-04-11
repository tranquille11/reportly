<?php

namespace App\Exports;

use App\Exports\Sheets\OrderSheet;
use App\Exports\Sheets\StoreSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class NeverShippedExport implements WithMultipleSheets
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new StoreSheet($this->data['Never Shipped'], 'NEVER SHIPPED & INCOMPLETE');
        $sheets[] = new OrderSheet($this->data['Orders'], 'ORDERS PER STORE');

        return $sheets;
    }
}
