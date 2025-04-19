<?php

use App\Actions\Reports\Appeasements\DataPerAppeasementReason;
use App\Exports\AppeasementBreakdownExport;
use App\Models\Brand;
use App\Traits\WithFilters;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

use function Livewire\Volt\{computed, state, updating, uses};

uses([WithFilters::class]);

state([
    'start' => now()->subDays(7)->startOfWeek()->format('Y-m-d'),
    'end' => now()->subDays(7)->endOfWeek()->format('Y-m-d'),
    'selectedBrand' => null
]);

updating([
    'start' => function () {
        unset($this->data);
    },
    'end' => function () {
        unset($this->data);
    },
]);

$data = computed(fn () => app(DataPerAppeasementReason::class)->handle($this->start, $this->end, $this->selectedBrand));

$export = function () {

    $brand = $this->selectedBrand ? Brand::whereName($this->selectedBrand)->first() : null;
    $name = "{$brand?->shorthand} Appeasements Breakdown " . Carbon::parse($this->start)->format('m.d') . "-" . Carbon::parse($this->end)->format('m.d') . ".xlsx";

    return Excel::download(new AppeasementBreakdownExport($this->data), $name);

};
?>


<div>
    <div class="flex items-center gap-2">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Appeasements breakdown</flux:breadcrumbs.item>
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

                <flux:menu.submenu heading="Brand">
                    <flux:menu.radio.group wire:model.live="selectedBrand">
                    @foreach(App\Models\Brand::all() as $brand)
                        <flux:menu.radio>{{ $brand->name }}</flux:menu.radio>
                    @endforeach
                    </flux:menu.radio.group>
                </flux:menu.submenu>
                
            </flux:menu>
        </flux:dropdown>
        <flux:button wire:click="export" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>       

    <div class="space-y-4">
        <div class="flex items-center mt-1 gap-2">
            <flux:badge variant="pill" size="sm" color="purple">Start date: {{$start}}</flux:badge>
            <flux:badge variant="pill" size="sm" color="purple">End date: {{$end}}</flux:badge>
            <flux:badge variant="pill" size="sm" color="purple">Brand: {{$selectedBrand ?? 'All brands'}}</flux:badge>
        </div>

        @if ($this->data)
            <flux:tab.group wire:key="{{now()}}">
                <div>
                    <flux:tabs>
                        @foreach (array_keys($this->data) as $tab)
                            <flux:tab name="{{$tab}}" class="border-b-[3.5px] !font-bold">{{str($tab)->lower()->ucfirst()}}</flux:tab>
                        @endforeach
                    </flux:tabs>
                </div>

                @foreach ($this->data as $tab => $data)
                    <flux:tab.panel name="{{$tab}}">
                            <flux:table>
                                <flux:table.columns>

                                    @if ($tab === 'GENERAL')
                                        <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                            Appeasement reason
                                            <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                        </flux:table.column>
                                    @elseif (in_array($tab, ["Manufacturer's defect", "PreOrder delay"]))
                                        <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                            Product
                                            <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                        </flux:table.column>
                                    @else
                                        <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                            Store #
                                            <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                        </flux:table.column>
                                    @endif

                                    <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                        # of refunds
                                        <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                    </flux:table.column>
                                    <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                        Amount
                                        <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                    </flux:table.column>
                                    <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                        % of total refunds
                                        <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                    </flux:table.column>

                                    <flux:table.column class="!px-6 !py-1.5 !font-bold">
                                        % of total amount
                                        <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                                    </flux:table.column>


                                </flux:table.columns>

                                @php
                                    $aggregates = [];
                                    $aggregates['count'] = collect($data)->pluck('# of refunds')->sum();
                                    $aggregates['amount'] = collect($data)->pluck('Amount')->sum();
                                @endphp

                                <flux:table.rows>
                                    <flux:table.row class="bg-zinc-700">
                                        <flux:table.cell class="!px-6 !py-1.5 !text-purple-400 font-extrabold">TOTAL</flux:table.cell>
                                        <flux:table.cell class="!px-6 !py-1.5 !text-purple-400 font-bold">{{$aggregates['count']}}</flux:table.cell>
                                        <flux:table.cell class="!px-6 !py-1.5 !text-purple-400 font-bold">${{number_format($aggregates['amount'], 2)}}</flux:table.cell>
                                        <flux:table.cell class="!px-6 !py-1.5 !text-purple-400 font-bold">100.00%</flux:table.cell>
                                        <flux:table.cell class="!px-6 !py-1.5 !text-purple-400 font-bold">100.00%</flux:table.cell>
                                    </flux:table.row>
                                    @foreach ($data as $row)
                                        <flux:table.row>
                                            @if ($tab === 'GENERAL')
                                                @if ($row['Appeasement reason'] == '')
                                                <flux:table.cell class="flex items-center gap-2 !px-6 !py-1.5 !text-red-400 font-medium">
                                                    <flux:icon.exclamation-triangle class="size-4" /> No reason specified
                                                </flux:table.cell>
                                                @else
                                                <flux:table.cell class="!px-6 !py-1.5">{{$row['Appeasement reason']}}</flux:table.cell>
                                                @endif
                                            @elseif (in_array($tab, ["Manufacturer's defect", "PreOrder delay"]))
                                                @if($row['products'] == '')
                                                <flux:table.cell class="flex items-center gap-2 !px-6 !py-1.5 !text-red-400 font-medium">
                                                    <flux:icon.exclamation-triangle class="size-4" /> No product specified
                                                </flux:table.cell>
                                                @else
                                                <flux:table.cell class="!px-6 !py-1.5">{{$row['products']}}</flux:table.cell>
                                                @endif
                                            @else
                                                @if($row['Store #'] == '')
                                                <flux:table.cell class="flex items-center gap-2 !px-6 !py-1.5 !text-red-400 font-medium">
                                                    <flux:icon.exclamation-triangle class="size-4" /> No location specified
                                                </flux:table.cell>
                                                @else
                                                <flux:table.cell class="!px-6 !py-1.5">{{$row['Store #']}}</flux:table.cell>
                                                @endif
                                            @endif

                                            <flux:table.cell class="!px-6 !py-1.5">{{$row['# of refunds']}}</flux:table.cell>
                                            <flux:table.cell class="!px-6 !py-1.5">${{number_format($row['Amount'], 2)}}</flux:table.cell>
                                            <flux:table.cell class="!px-6 !py-1.5">{{ number_format(($row['# of refunds'] / $aggregates['count']) * 100, 2) }}%</flux:table.cell>
                                            <flux:table.cell class="!px-6 !py-1.5">{{ number_format(($row['Amount'] / $aggregates['amount']) * 100, 2) }}%</flux:table.cell>

                                        </flux:table.row>
                                    @endforeach

                                </flux:table.rows>
                            </flux:table>
                    </flux:tab.panel>
                @endforeach
            </flux:tab.group>
        @else
            <div class="flex justify-center mt-20">
                <div class="space-y-6 w-96 text-center">
                    <flux:icon.circle-dollar-sign class="mx-auto size-8 text-purple-300"/>
                    <flux:text>No appeasements found during the period. Start by importing appeasements.</flux:text>
                    <flux:button class="mx-auto" icon:trailing="arrow-top-right-on-square" href="{{ route('appeasements') }}" wire:navigate> Import Appeasements</flux:button>
                </div>
            </div>
        @endif

    </div>
</div>
