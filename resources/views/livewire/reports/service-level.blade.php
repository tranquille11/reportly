<?php

use App\Traits\WithFilters;
use Illuminate\Support\Carbon;
use App\Exports\ServiceLevelExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Actions\Reports\ServiceLevel\OverallServiceLevelData;
use App\Actions\Reports\ServiceLevel\ServiceLevelDataPerBrand;
use App\Actions\Reports\ServiceLevel\PrepareServiceLevelDataForExport;

use function Livewire\Volt\{computed, state, uses};

uses(WithFilters::class);

state([
    'start' => now()->subDays(7)->startOfWeek()->format('Y-m-d'),
    'end' => now()->subDays(7)->endOfWeek()->format('Y-m-d'),
    'threshold' => 120
]);

$data = computed(fn () => app(ServiceLevelDataPerBrand::class)->handle($this->start, $this->end, $this->threshold));

$overall = computed(fn () => app(OverallServiceLevelData::class)->handle($this->data));

$export = function () {
    
    $data = app(PrepareServiceLevelDataForExport::class)->handle($this->data);
    
    return Excel::download(new ServiceLevelExport($data), 'Service_Level ' . Carbon::parse($this->start)->format('m.d') . "-" . Carbon::parse($this->end)->format('m.d') . ".xlsx");
};

?>

<div>
    <div class="flex items-center gap-2">

    <flux:breadcrumbs>
        <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
        <flux:breadcrumbs.item>Service level</flux:breadcrumbs.item>
    </flux:breadcrumbs>

    <flux:spacer />

    <flux:dropdown>
        
        <flux:button icon-trailing="chevron-down" size="sm" variant="filled">Filters</flux:button>

        <flux:menu>
            <flux:menu.submenu  heading="Start date">
                <flux:calendar wire:model.live="start" />
            </flux:menu.submenu>
            
            <flux:menu.submenu  heading="End date">
                <flux:calendar wire:model.live="end" />
            </flux:menu.submenu>

            <flux:menu.submenu heading="Threshold">
                <flux:input wire:model.live.debounce="threshold"/>
            </flux:menu.submenu>

        </flux:menu>
    </flux:dropdown>

    <flux:button wire:click="export" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>     

    <div class="flex items-center mt-1 gap-2 mb-6">
        <flux:badge variant="pill" size="sm" color="amber">Start date: {{$start}}</flux:badge>
        <flux:badge variant="pill" size="sm" color="amber">End date: {{$end}}</flux:badge>
        <flux:badge variant="pill" size="sm" color="amber">Threshold: {{ $threshold }} seconds</flux:badge>
    </div>

    <div class="grid grid-cols-3 gap-4 py-3">
        @foreach ($this->overall as $name => $number)
            <flux:card wire:target="start, end" wire:loading.class="opacity-70" class="!relative !shadow-md">
                <div wire:target="start, end" wire:loading class="absolute top-1/2 left-1/2">
                    <flux:icon.loading/>
                </div>
                <flux:subheading>{{$name}}</flux:subheading>
                <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                    <flux:icon.newspaper class="size-4 text-amber-400"/>
                    {{$number}}
                </flux:heading>
            </flux:card>
        @endforeach
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2 !py-1.5">Brand</flux:table.column>
            <flux:table.column class="!px-2 !py-1.5">Total calls</flux:table.column>
            <flux:table.column class="!px-2 !py-1.5">Service level</flux:table.column>
            <flux:table.column class="!px-2 !py-1.5">Score</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->data as $brand)
                <flux:table.row>
                    <flux:table.cell variant="strong" class="!px-2 !py-1.5">{{$brand['brand']}}</flux:table.cell>
                    <flux:table.cell class="!px-2 !py-1.5">{{$brand['total_calls']}}</flux:table.cell>
                    <flux:table.cell class="!px-2 !py-1.5">{{$brand['service_level']}}</flux:table.cell>
                    <flux:table.cell class="!px-2 !py-1.5">{{$brand['score']}}</flux:table.cell>
                </flux:table.row>
            @endforeach

        </flux:table.rows>
    </flux:table>
</div>
