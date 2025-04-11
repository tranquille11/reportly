<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReturnsUnlocksSheet implements FromArray, WithColumnWidths, WithHeadings, WithStrictNullComparison, WithTitle
{
    private array $data;

    private string $name;

    public function __construct(array $data, string $name)
    {
        $this->data = $data;
        $this->name = $name;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 8,
            'C' => 21,
            'D' => 11,
        ];
    }

    public function headings(): array
    {
        return [
            'Order #',
            'Store #',
            'Item',
            'Date',
        ];
    }

    public function title(): string
    {
        return $this->name;
    }
}
