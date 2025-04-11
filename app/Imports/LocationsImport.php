<?php

namespace App\Imports;

use App\Exceptions\ImportHeadingColumnException;
use App\Models\Location;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;

class LocationsImport implements ToModel, WithBatchInserts, WithHeadingRow, WithUpserts
{
    use Importable;

    protected $columns = [
        'name',
        'number',
        'type',
    ];

    public function model(array $row)
    {
        $headings = array_keys($row);
        $diff = collect($this->columns)->diff($headings);

        if ($diff->isNotEmpty()) {
            throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
        }

        return new Location([
            'name' => trim($row['name']),
            'number' => trim($row['number']),
            'type' => trim($row['type']),
        ]);
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function uniqueBy()
    {
        return 'number';
    }
}
