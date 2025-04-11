<?php

namespace App\Imports;

use App\Enums\AppeasementStatus;
use App\Enums\ImportHistoryStatus;
use App\Enums\TagType;
use App\Exceptions\ImportHeadingColumnException;
use App\Models\Appeasement;
use App\Models\AppeasementReason;
use App\Models\Brand;
use App\Models\ImportHistory;
use App\Models\Location;
use App\Notifications\ImportFailedNotification;
use App\Notifications\ImportFinishedNotification;
use App\Services\AppeasementService;
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
use Spatie\Tags\Tag;

class AppeasementsImport implements ToModel, WithBatchInserts, WithChunkReading, WithEvents, WithHeadingRow, WithUpserts
{
    use Importable;

    private $reasons;

    private $locations;

    private $sizes;

    private $colors;

    private $delimiters;

    protected bool $hasAppropriateColumns = false;

    public $startDate = null;

    public $endDate = null;

    protected $columns = [
        'iscancel',
        'refundid',
        'orderid',
        'ordernumber',
        'total_refund',
        'refund_date',
        'refund_note',
    ];

    public function __construct(public Brand $brand, public ImportHistory $history)
    {
        $this->reasons = AppeasementReason::all();
        $this->locations = Location::with('parent')->get();
        $this->sizes = Tag::getWithType(TagType::PRODUCT_SIZE->value)->pluck('name')->toArray();
        $this->colors = Tag::getWithType(TagType::PRODUCT_COLOR->value)->pluck('name')->toArray();
        $this->delimiters = Tag::getWithType(TagType::PRODUCT_DELIMITER->value)->pluck('name')->toArray();
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
            (in_array($row['refund_note'], ['Order canceled', 'NULL']) && $row['iscancel'] == 1) ||
            ($row['total_refund'] * 100 == 0)
        ) {
            return null;
        }

        $appeasementDate = Carbon::parse(($row['refund_date'] - 25569) * 86400);

        if (is_null($this->startDate) && is_null($this->endDate)) {
            $this->startDate = $appeasementDate;
            $this->endDate = $appeasementDate;
        }

        if ($appeasementDate->isBefore($this->endDate)) {
            $this->startDate = $appeasementDate;
        } else {
            $this->endDate = $appeasementDate;
        }

        $appeasement = new Appeasement([
            'unique_id' => $row['refundid'].$row['orderid'],
            'order_id' => $row['orderid'],
            'order_number' => $row['ordernumber'],
            'amount' => $row['total_refund'] * 100,
            'date' => $appeasementDate->toDateString(),
            'note' => strtoupper($row['refund_note']),
            'brand_id' => $this->brand->id,
            'location_id' => null,
            'reason_id' => null,
            'status_message' => null,
            'status' => AppeasementStatus::FAILED,
            'products' => [],
        ]);

        $service = new AppeasementService(
            appeasement: $appeasement,
            reasons: $this->reasons,
            locations: $this->locations,
            sizes: $this->sizes,
            colors: $this->colors,
            delimiters: $this->delimiters,
        );

        $appeasement = $service->process()->appeasement();

        return $appeasement;
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function uniqueBy()
    {
        return 'unique_id';
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
            },
            ImportFailed::class => function (ImportFailed $e) {
                $this->history->update([
                    'status' => ImportHistoryStatus::FAILED,
                    'message' => $e->getException()->getMessage(),
                ]);
                $this->history->user->notify(new ImportFailedNotification(ucfirst($this->history->type->value)));
            },
        ];
    }
}
