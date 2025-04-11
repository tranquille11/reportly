<?php

namespace App\Imports;

use App\Enums\ImportHistoryStatus;
use App\Enums\TagType;
use App\Exceptions\ImportHeadingColumnException;
use App\Models\ImportHistory;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Spatie\Tags\Tag;

class TagsImport implements ToModel, WithBatchInserts, WithEvents, WithHeadingRow
{
    use Importable;

    protected $columns = [
        'name',
        'type',
    ];

    public function __construct(public ImportHistory $history) {}

    public function model(array $row)
    {
        $headings = array_keys($row);
        $diff = collect($this->columns)->diff($headings);

        if ($diff->isNotEmpty()) {
            throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
        }

        return new Tag([
            'name' => trim($row['name']),
            'slug' => Str::slug(trim($row['name']), '-'),
            'type' => TagType::tryFrom(trim($row['type']))?->value,
        ]);
    }

    public function batchSize(): int
    {
        return 50;
    }

    public function uniqueBy()
    {
        return 'name';
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->history->update([
                    'status' => ImportHistoryStatus::COMPLETED,
                ]);
                $this->history->user->notify(new ImportFinishedNotification(ucfirst($this->history->type->value)));
            },
            ImportFailed::class => function (ImportFailed $e) {
                $this->history->update([
                    'status' => ImportHistoryStatus::FAILED,
                    'message' => $e->getException()->getMessage(),
                ]);
                $this->history->user->notify(new ImportFailedNotification($this->history->type->value));
            },
        ];
    }
}
