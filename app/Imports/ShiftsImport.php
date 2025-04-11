<?php

namespace App\Imports;

use App\Enums\ImportHistoryStatus;
use App\Exceptions\ImportHeadingColumnException;
use App\Jobs\ShiftsDataAggregation;
use App\Models\Agent;
use App\Models\ImportHistory;
use App\Models\Shift;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Carbon\CarbonImmutable;
use Exception;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class ShiftsImport implements ToModel, WithBatchInserts, WithEvents, WithHeadingRow
{
    use Importable;

    protected $columns = [
        'name',
        'agent_id',
        'start_date',
        'end_date',
        'shifts',
    ];

    protected $startDate;

    protected $endDate;

    protected $agents;

    public function __construct(public ImportHistory $history)
    {
        $this->agents = Agent::with('shifts')->get();
    }

    public function model(array $row)
    {
        $headings = array_keys($row);
        $diff = collect($this->columns)->diff($headings);

        if ($diff->isNotEmpty()) {
            throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
        }

        $agent = $this->agents->firstWhere('id', $row['agent_id']);
        $start = CarbonImmutable::parse($row['start_date']);
        $end = CarbonImmutable::parse($row['end_date']);
        $shifts = (int) trim($row['shifts']);

        if (! $agent) {
            throw new Exception('Agent with id ['.$row['agent_id'].'] does not exist in database.');
        }

        if ($agent->name != $row['name']) {
            throw new Exception('Agent with id ['.$row['agent_id'].'] was found in database however, the name does not coincide ['.$agent->name.']');
        }

        if (! $start->isSameDay($start->firstOfMonth()) || ! $end->isSameDay($end->endOfMonth())) {
            throw new Exception('Start/end dates need to be exactly first/last day of the same month');
        }

        if (! $start->isSameMonth($end)) {
            throw new Exception('Start and end dates need to be within the same month');
        }

        if ($start->isFuture() || $end->isFuture()) {
            throw new Exception('Start/end dates cannot be in the future');
        }

        if ($agent->shifts->where('start_date', $row['start_date'])->where('end_date', $row['end_date'])->isNotEmpty()) {
            throw new Exception("Agent [{$agent->name}] already has shifts in database for [{$row['start_date']} - {$row['end_date']}]");
        }

        if ($shifts < 1 || ! is_int($shifts)) {
            throw new Exception('Shifts field must be a number greater than 0.');
        }

        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');

        return new Shift([
            'agent_id' => (int) trim($row['agent_id']),
            'number' => (int) trim($row['shifts']),
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
        ]);
    }

    public function batchSize(): int
    {
        return 150;
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->history->update([
                    'status' => ImportHistoryStatus::COMPLETED,
                ]);

                $this->history->user->notify(new ImportFinishedNotification(ucfirst($this->history->type->value)));

                ShiftsDataAggregation::dispatch($this->startDate, $this->endDate);
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
