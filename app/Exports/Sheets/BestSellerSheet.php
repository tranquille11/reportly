<?php

namespace App\Exports\Sheets;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class BestSellerSheet implements FromArray, ShouldAutoSize, WithEvents, WithHeadings, WithTitle
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

    public function title(): string
    {
        return $this->name;
    }

    public function headings(): array
    {

        return [
            'Add',
            'Remove',
            'Extra items',
        ];
    }

    public function registerEvents(): array
    {
        return [];
    }
}
