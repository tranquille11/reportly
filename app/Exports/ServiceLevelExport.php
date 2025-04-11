<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Events\AfterSheet;

class ServiceLevelExport implements FromArray, ShouldAutoSize, WithCustomStartCell, WithEvents, WithStrictNullComparison
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        return $this->data;
    }

    public function startCell(): string
    {
        return 'B1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->getDelegate()->setCellValue('A2', 'Total calls');
                $event->getDelegate()->setCellValue('A3', 'Service level');
            },
        ];
    }
}
