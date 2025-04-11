<?php

namespace App\Exports\Sheets;

use App\Exports\Traits\WithFormatAppeasementSheets;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class GeneralSheet implements FromArray, WithColumnWidths, WithEvents, WithHeadings, WithStrictNullComparison, WithTitle
{
    use WithFormatAppeasementSheets;

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
            'A' => 26,
            'B' => 16,
            'C' => 20,
            'D' => 24,
            'E' => 24,
        ];
    }

    public function headings(): array
    {
        return [
            'Appeasement note',
            '# of refunds',
            'Amount refunded',
            '% of total refunds count',
            '% of total dollar amount',
        ];
    }

    public function title(): string
    {
        return $this->name;
    }
}
