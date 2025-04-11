<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class GorgiasReasonsForContactExport implements FromArray, WithHeadings, WithStrictNullComparison
{
    private array $brands;

    private array $data;

    public function __construct(array $data, array $brands)
    {
        $this->brands = $brands;
        $this->data = $data;
        array_unshift($this->brands, '');
    }

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return $this->brands;
    }
}
