<?php

namespace App\Imports;

use App\Enums\ImportHistoryStatus;
use App\Exceptions\ImportHeadingColumnException;
use App\Models\Agent;
use App\Models\ImportHistory;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use App\Rules\AgentsRoleRule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class AgentsImport implements ToModel, WithBatchInserts, WithEvents, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    protected $columns = [
        'name',
        'stage_name',
        'email',
        'role',
    ];

    public function __construct(public ImportHistory $history) {}

    public function model(array $row)
    {
        $headings = array_keys($row);
        $diff = collect($this->columns)->diff($headings);

        if ($diff->isNotEmpty()) {
            throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
        }

        return new Agent([
            'name' => trim($row['name']),
            'stage_name' => trim($row['stage_name']),
            'email' => trim($row['email']),
            'role' => trim($row['role']),
            'settings' => ['gorgias_user_id' => null],
        ]);
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function uniqueBy(): string
    {
        return 'email';
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

    public function rules(): array
    {
        return [
            'email' => 'email',
            'role' => new AgentsRoleRule,
        ];
    }
}
