<?php

namespace App\Imports;

use App\Enums\ImportHistoryStatus;
use App\Exceptions\AgentNotFoundException;
use App\Exceptions\BrandNotFoundException;
use App\Exceptions\ImportHeadingColumnException;
use App\Helpers\Helper;
use App\Jobs\TalkdeskDataAggregation;
use App\Models\Agent;
use App\Models\Brand;
use App\Models\Call;
use App\Models\Disposition;
use App\Models\ImportHistory;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\ImportFailed;

class CallsImport implements ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts
{
    use Importable;

    private $agents;

    private $brands;

    private $dispositions;

    public $startDate = null;

    public $endDate = null;

    protected bool $hasAppropriateColumns = false;

    protected $columns = [
        'interaction_id',
        'call_type',
        'start_time',
        'end_time',
        'customer_phone_number',
        'talk_time',
        'waiting_time',
        'holding_time',
        'agent_disconnected',
        'record',
    ];

    public function __construct(public ImportHistory $history)
    {
        $this->agents = Agent::all();
        $this->brands = Brand::all();
    }

    public function model(array $row)
    {
        if (! $this->hasAppropriateColumns) {
            $headings = array_keys($row);
            $diff = collect($this->columns)->diff($headings);

            if ($diff->isNotEmpty()) {
                throw new ImportHeadingColumnException('Columns '.$diff->join(', ').' do not exist in report.');
            }

            $this->hasAppropriateColumns = true;
        }

        if (
            in_array($row['call_type'], ['abandoned', 'short_abandoned', 'outbound']) ||
            in_array($row['tags'], ['researchers', 'supervisors', 'testing'])
        ) {
            return null;
        }

        $row = collect($row)->map(function ($cell) {
            if (! is_int($cell)) {
                $cell = str($cell)->trim()->toString();
            }

            if ($cell == '') {
                $cell = null;
            }

            return $cell;
        })->toArray();

        $callDate = Carbon::parse($row['start_time']);

        if (is_null($this->startDate) && is_null($this->endDate)) {
            $this->startDate = $callDate;
            $this->endDate = $callDate;
        }

        if ($callDate->isBefore($this->endDate)) {
            $this->startDate = $callDate;
        } else {
            $this->endDate = $callDate;
        }

        $brand = $this->processBrandByTag($row['tags']);
        $agent = $this->processAgentName($row['agent_name']);
        $handlingAgent = $this->processAgentName($row['handling_agent']);
        $disposition = $this->processDisposition($row['disposition_code']);

        return new Call([
            'interaction_id' => $row['interaction_id'],
            'call_type' => $row['call_type'],
            'start_time' => Carbon::parse($row['start_time'])->format('Y-m-d H:i:s'),
            'end_time' => Carbon::parse($row['end_time'])->format('Y-m-d H:i:s'),
            'phone_number' => str_replace("'", '', $row['customer_phone_number']),
            'talk_time' => Helper::stringToTime($row['talk_time']),
            'wait_time' => Helper::stringToTime($row['waiting_time']),
            'hold_time' => Helper::stringToTime($row['holding_time']),
            'agent_disconnected' => ! is_null(['agent_disconnected']) ? ($row['agent_disconnected'] == 'Yes' ? true : false) : null,
            'recording' => $row['record'] ?? null,
            'brand_id' => $brand->id,
            'agent_id' => $agent?->id,
            'handling_agent_id' => $handlingAgent?->id,
            'disposition_id' => $disposition?->id,
        ]);
    }

    public function chunkSize(): int
    {
        return 1500;
    }

    public function batchSize(): int
    {
        return 1500;
    }

    public function uniqueBy()
    {
        return 'interaction_id';
    }

    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {

                $this->history->update([
                    'status' => ImportHistoryStatus::COMPLETED,
                    'start_date' => $this->startDate?->toDateString(),
                    'end_date' => $this->endDate?->toDateString(),
                ]);

                $this->history->user->notify(new ImportFinishedNotification(ucfirst($this->history->type->value)));

                TalkdeskDataAggregation::dispatch();

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

    protected function processAgentName(?string $name): ?Agent
    {
        if (is_null($name) || in_array($name, ['External Phone Number', 'DELETED AGENT'])) {
            return null;
        }

        $agent = $this->agents->firstWhere('name', $name);

        if (! $agent) {
            throw new AgentNotFoundException("Agent with name [$name] does not exist.");
        }

        return $agent;
    }

    protected function processBrandByTag(string $tag): Brand
    {
        $brand = Brand::withAnyTags([$tag], 'talkdesk')->first();

        if (! $brand) {
            throw new BrandNotFoundException("Talkdesk tag [$tag] is not assigned to a brand.");
        }

        return $brand;
    }

    protected function processDisposition(?string $name): ?Disposition
    {
        if (is_null($name)) {
            return null;
        }

        $disposition = Disposition::where('name', $name)->first();

        if (is_null($disposition)) {
            $disposition = Disposition::create(['name' => $name]);
        }

        return $disposition;
    }
}
