<?php

use App\Actions\Reports\Appeasements\AppeasementsPerMonth;
use App\Exports\AppeasementsPerMonthExport;
use App\Traits\WithFilters;
use Maatwebsite\Excel\Facades\Excel;

use function Livewire\Volt\{computed, state, updating, uses};

uses([WithFilters::class]);

state([
    'start' => now()->subMonths(7)->firstOfMonth()->format('Y-m-d'),
    'end' => now()->subMonth()->endOfMonth()->format('Y-m-d'),
]);

updating([
    'start' => function () {
        unset($this->data);
    },
    'end' => function () {
        unset($this->data);
    },
]);

$data = computed(fn () => app(AppeasementsPerMonth::class)->handle($this->start, $this->end));

$export = function () {

    $name = "Appeasements per month_" . now()->timestamp .  ".xlsx";

    return Excel::download(new AppeasementsPerMonthExport($this->data), $name);

};

?>

<div>

    <div class="flex items-center gap-2">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Appeasements per month</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <flux:dropdown>
            
            <flux:button icon-trailing="chevron-down" size="sm" variant="filled">Filters</flux:button>

            <flux:menu>

                <flux:menu.submenu heading="Start date">
                    <flux:calendar wire:model.live="start"/>
                </flux:menu.submenu>

                <flux:menu.submenu heading="End date">
                    <flux:calendar wire:model.live="end"/>
                </flux:menu.submenu>
                
            </flux:menu>
        </flux:dropdown>
        <flux:button wire:click="export" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>    

    <div class="space-y-4">
        <div class="flex items-center mt-1 gap-2">
            <flux:badge variant="pill" size="sm" color="amber">Start date: {{$start}}</flux:badge>
            <flux:badge variant="pill" size="sm" color="amber">End date: {{$end}}</flux:badge>
        </div>
    @if ($this->data)
            <flux:tab.group wire:key="{{now()}}">
                <div>
                    <flux:tabs>
                        @foreach (array_keys($this->data['data']) as $tab)
                            <flux:tab name="{{$tab}}" class="border-b-[3.5px] !font-bold">{{$tab}}</flux:tab>
                        @endforeach
                    </flux:tabs>
                </div>

                @foreach ($this->data['data'] as $tab => $data)
                    <flux:tab.panel name="{{$tab}}">
                            <flux:table>
                                <flux:table.columns>
                                    <flux:table.column class="!px-2 !py-1.5 !font-bold">
                                        Appeasement Reason
                                    </flux:table.column>
                                    @foreach($this->data['dates'] as $month)
                                    <flux:table.column class="!px-2 !py-1.5 !font-bold">
                                        {{ $month }}
                                    </flux:table.column>
                                    @endforeach
                                    <flux:table.column class="!px-2 !py-1.5 !font-bold">Trend</flux:table.column>

                                </flux:table.columns>
                                
                                <flux:table.rows>
                                    @foreach ($data as $row)
                                        <flux:table.row>
                                            @foreach($row as $cell)
                                                <flux:table.cell class="!px-2 !py-1.5"> {{$cell}}</flux:table.cell>
                                            @endforeach

                                            @php
                                                array_shift($row);
                                                $row = array_values($row);

                                                $stats = Arr::take($row, -4);

                                            @endphp
                                            <flux:table.cell>
                                                <flux:chart :value="$row" class="w-[2rem] aspect-[3/1]">
                                                    <flux:chart.svg gutter="0">
                                                        <flux:chart.line class=" {{ $stats[0] > $stats[count($stats)-1] ? 'text-green-500 dark:text-green-400' : 'text-red-500 dark:text-red-400'}}" />
                                                    </flux:chart.svg>
                                                </flux:chart>
                                            </flux:table.cell>
                                        </flux:table.row>
                                    @endforeach

                                </flux:table.rows>
                            </flux:table>
                    </flux:tab.panel>
                @endforeach

            </flux:tab.group>
        @endif
    </div>
</div>
