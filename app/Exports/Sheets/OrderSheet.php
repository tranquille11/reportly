<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class OrderSheet implements FromArray, WithColumnWidths, WithStrictNullComparison, WithTitle
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
            'B' => 15,
            'C' => 15,
            'D' => 15,
            'E' => 15,
        ];
    }

    public function title(): string
    {
        return $this->name;
    }
}
