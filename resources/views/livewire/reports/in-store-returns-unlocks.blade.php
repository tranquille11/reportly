<?php

use App\Actions\Reports\Appeasements\ReturnsAndUnlocksData;
use App\Exports\ReturnsAndUnlocksExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Traits\WithFilters;
use Illuminate\Support\Carbon;

use function Livewire\Volt\{computed, state, uses, updating};

uses(WithFilters::class);

state([
    'start' => now()->subDays(7)->startOfWeek()->format('Y-m-d'),
    'end' => now()->subDays(7)->endOfWeek()->format('Y-m-d'),
]);

updating(['start' => function () {
    unset($this->data);
}, 'end' => function () {    
    unset($this->data);
}]);

$data = computed(fn () => app(ReturnsAndUnlocksData::class)->handle($this->start, $this->end));

$export = function () {
    $name = "Returns&Unlocks " . Carbon::parse($this->start)->format('m.d') . "-" . Carbon::parse($this->end)->format('m.d') . ".xlsx";

    return Excel::download(new ReturnsAndUnlocksExport($this->data), $name);
}
?>

<div>
    <div class="flex items-center gap-2">

        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>In-store returns & unlocks</flux:breadcrumbs.item>
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
                    <flux:select variant="listbox" placeholder="Choose brand..." wire:model.live="selectedBrand">
                        @foreach(App\Models\Brand::all() as $brand)
                            <flux:select.option>{{ $brand->name }}</flux:select.option>
                        @endforeach
                    </flux:select>
                </flux:menu.submenu>
            </flux:menu>
        </flux:dropdown>

        <flux:button wire:click="export" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>      

    <div class="space-y-4">
        <div class="flex items-center mt-1 gap-2">
            <flux:badge variant="pill" size="sm" color="amber">Start date: {{$start}}</flux:badge>
            <flux:badge variant="pill" size="sm" color="amber">End date: {{$end}}</flux:badge>
            <flux:badge variant="pill" size="sm" color="amber">Brand: {{$selectedBrand ?? 'All brands'}}</flux:badge>
        </div>

        @if($this->data)
        <flux:tab.group>
            <div>
                <flux:tabs>
                    @foreach (array_keys($this->data) as $tab)
                        <flux:tab name="{{$tab}}" wire:key="{{$tab}}" class="border-b-[3.5px] !font-bold">{{str($tab)->lower()->ucfirst()}}</flux:tab>
                    @endforeach
                </flux:tabs>
            </div>
            @foreach ($this->data as $tab => $data)
            <flux:tab.panel name="{{$tab}}">
                <flux:table>
                    <flux:table.columns>
                        
                        <flux:table.column class="!px-2 !py-1.5 !font-bold">
                            Order #
                        </flux:table.column>
                        <flux:table.column class="!px-2 !py-1.5 !font-bold">
                            Store
                        </flux:table.column>
                        <flux:table.column class="!px-2 !py-1.5 !font-bold">
                            Product
                        </flux:table.column>

                        <flux:table.column class="!px-2 !py-1.5 !font-bold">
                            Date
                        </flux:table.column>

                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach($data as $row)
                        <flux:table.row>
                            <flux:table.cell class="!px-2 !py-1.5" variant="strong">{{$row['order_number']}}</flux:table.cell>
                            <flux:table.cell class="!px-2 !py-1.5">{{ $row['store'] }}</flux:table.cell>
                            <flux:table.cell class="!px-2 !py-1.5">{{ $row['product'] }}</flux:table.cell>
                            <flux:table.cell class="!px-2 !py-1.5">{{ $row['date'] }}</flux:table.cell>
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
