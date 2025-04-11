<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;

class AgentsOverviewExport implements FromArray, WithHeadings, WithStrictNullComparison
{
    public function __construct(private array $data) {}

    public function array(): array
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Agent name',
            'Total calls',
            'No disposition',
            'Outbound missed',
            'Hung up by agent quickly',
            'High hold time',
            'High talk time',
            'Emails closed',
            'Chats closed',
            'Email rating',
            'Chat rating',
            'Onetouch rate',
        ];
    }
}
