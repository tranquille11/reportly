<?php

namespace App\Jobs;

use App\Enums\ReasonsForContact;
use App\Exports\GorgiasReasonsForContactExport;
use App\Models\Disposition;
use App\Models\Report;
use App\Services\GorgiasService;
use Carbon\CarbonImmutable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Sleep;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Tags\Tag;

class GorgiasReasonsForContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private array $brands;

    private Collection $numbers;

    private $dispositions;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $start, public string $end) {}

    public function handle(GorgiasService $gorgias)
    {

        $this->brands = Tag::getWithType('gorgias-brand')->pluck('name')->all();

        $this->dispositions = Disposition::with('tags')
            ->get()
            ->map(function ($disp) {
                $disp->tagNames = $disp->tags->pluck('name')->all();

                return $disp;
            });

        $arr = [];

        foreach ($this->brands as $brand) {
            foreach ($this->dispositions as $disposition) {
                $arr[$brand][$disposition->name] = 0;
            }
        }

        $this->numbers = collect($arr);

        $start = CarbonImmutable::parse($this->start)->startOfDay();
        $end = CarbonImmutable::parse($this->end)->endOfDay();

        $view = $gorgias->createRFCView($start->toAtomString(), $end->toAtomString());

        if (! $view->created()) {
            exit;
        }

        $viewId = $view->json()['id'];

        $nextCursor = null;

        try {
            while (true) {
                $tickets = $gorgias->fetchViewTickets(id: $viewId, cursor: $nextCursor);
                $this->processTickets($tickets['data']);

                $nextCursor = $tickets['meta']['next_cursor'];

                if (! $nextCursor) {
                    break;
                }

                usleep(500000);
            }
        } finally {
            $gorgias->deleteView($viewId);
        }

        $data = [];

        foreach ($this->dispositions as $disposition) {
            $this->numbers->each(function ($item, $brand) use ($disposition, &$data) {
                foreach ($item as $name => $val) {
                    if (! isset($data[$disposition->name][$brand])) {
                        $data[$disposition->name]['reason'] = $disposition->name;
                    }
                    if ($name === $disposition->name) {
                        $data[$disposition->name][$brand] = $val;
                    }
                }
            });
        }

        $report = Report::where('slug', 'reasons-for-contact')->first();
        $filename = "Reasons for contact {$start->format('m.d')} - {$end->format('m.d')}.xlsx";

        Excel::store(new GorgiasReasonsForContactExport($data, $this->brands), "reports/{$filename}");

        $report->files()->create(['file' => $filename]);
    }

    private function processTickets($tickets): void
    {
        foreach ($tickets as $key => $ticket) {
            $currentBrand = '';
            $reasonsForContact = [];
            foreach ($ticket['tags'] as $tag) {

                $reason = $this->dispositions->first(function ($value, $key) use ($tag) {
                    return in_array($tag['name'], $value->tagNames);
                })?->name;

                if (in_array($tag['name'], $this->brands)) {
                    $currentBrand = $tag['name'];
                }

                if ($reason && ! in_array($reason, $reasonsForContact)) {
                    $reasonsForContact[] = $reason;
                }
            }

            $this->numbers->transform(function ($item, $key) use ($currentBrand, $reasonsForContact) {
                if ($key === $currentBrand && ! empty($reasonsForContact)) {
                    foreach ($reasonsForContact as $reason) {
                        $item[$reason] += 1;
                    }
                }

                return $item;
            });
        }
    }
    /**
     * Execute the job.
     */
    // public function handle(): void
    // {
    //     $start = CarbonImmutable::parse($this->start)->startOfDay();
    //     $end = CarbonImmutable::parse($this->end)->endOfDay();

    //     $service = new GorgiasService($start, $end);

    //     $cases = ReasonsForContact::cases();
    //     $arr = [];

    //     foreach ($this->brands as $brand) {
    //         foreach ($cases as $case) {
    //             $arr[$brand][$case->value] = 0;
    //         }
    //     }

    //     $this->numbers = collect($arr);
    //     $nextCursors = true;

    //     while ($nextCursors !== null) {
    //         $responses = $service->fetchViews($nextCursors);

    //         $nextCursors = [];

    //         foreach ($responses as $name => $response) {
    //             $response = json_decode($response);

    //             if (! $response) {
    //                 $nextCursors[$name] = null;

    //                 continue;
    //             }

    //             $nextCursors[$name] = $response->meta->next_cursor;

    //             $this->processTickets($response->data);
    //         }

    //         if (empty(array_filter($nextCursors, fn ($item) => ! is_null($item)))) {
    //             $nextCursors = null;
    //         }

    //         sleep(2);
    //         // usleep(500000)
    //     }

    //     $data = [];

    //     foreach ($cases as $case) {
    //         $this->numbers->each(function ($item, $brand) use ($case, &$excelData) {
    //             foreach ($item as $name => $val) {
    //                 if (! isset($excelData[$case->value][$brand])) {
    //                     $excelData[$case->value]['reason'] = $case->value;
    //                 }
    //                 if ($name === $case->value) {
    //                     $excelData[$case->value][$brand] = $val;
    //                 }
    //             }
    //         });
    //     }

    //     $name = "[Gorgias] Reasons for contact {$start->format('m.d')}-{$end->format('m.d')}.csv";
    //     $path = "rfc/{$name}";

    //     Excel::store(new GorgiasReasonsForContactExport($excelData, $this->brands), $path);

    //     $this->report->logs()->create([
    //         'name' => $name,
    //         'file' => $path,
    //     ]);
    // }

}
