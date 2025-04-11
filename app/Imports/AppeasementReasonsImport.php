<?php

namespace App\Imports;

use App\Exceptions\ImportHeadingColumnException;
use App\Models\AppeasementReason;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class AppeasementReasonsImport implements ToModel, WithBatchInserts, WithHeadingRow, WithUpserts
{
    use Importable;

    protected $columns = [
        'name',
        'shorthand',
        'has_percentage',
        'has_location',
        'has_product',
        'has_size',
    ];

    public function model(array $row)
    {
        $headings = array_keys($row);
        $diff = collect($this->columns)->diff($headings);

        if ($diff->isNotEmpty()) {
            throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
        }

        return new AppeasementReason([
            'name' => trim($row['name']),
            'shorthand' => trim($row['shorthand']),
            'has_percentage' => trim($row['has_percentage']),
            'has_location' => trim($row['has_location']),
            'has_product' => trim($row['has_product']),
            'has_size' => trim($row['has_size']),
        ]);
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function uniqueBy()
    {
        return 'shorthand';
    }
}
