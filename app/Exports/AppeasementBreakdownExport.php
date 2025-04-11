<?php

namespace App\Exports;

use App\Exports\Sheets\GeneralSheet;
use App\Exports\Sheets\ItemSheet;
use App\Exports\Sheets\StoreSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AppeasementBreakdownExport implements WithMultipleSheets
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new GeneralSheet($this->data['GENERAL'], 'GENERAL');
        $sheets[] = new ItemSheet($this->data['PreOrder delay'] ?? [], 'PREORDER DELAY');
        $sheets[] = new ItemSheet($this->data['Manufacturer\'s defect'] ?? [], 'MANUFACTURER\'S DEFECT');
        $sheets[] = new StoreSheet($this->data['Never Shipped'] ?? [], 'NEVER SHIPPED');
        $sheets[] = new StoreSheet($this->data['Incomplete shipment'] ?? [], 'INCOMPLETE SHIPMENTS');
        $sheets[] = new StoreSheet($this->data['Unlocks'] ?? [], 'UNLOCKS');

        return $sheets;
    }
}
