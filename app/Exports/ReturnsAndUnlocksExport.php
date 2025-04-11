<?php

namespace App\Exports;

use App\Exports\Sheets\ReturnsUnlocksSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class ReturnsAndUnlocksExport implements WithMultipleSheets
{
    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new ReturnsUnlocksSheet($this->data['In-store returns'] ?? [], 'INSTORE RETURNS');
        $sheets[] = new ReturnsUnlocksSheet($this->data['Unlocks'] ?? [], 'UNLOCKS');

        return $sheets;
    }
}
