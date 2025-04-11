<?php

use Flux\Flux;
use App\Models\Brand;
use App\Models\Appeasement;
use App\Traits\WithFilters;
use App\Pipes\Filters\Search;
use App\Pipes\Filters\OrderBy;
use App\Models\AppeasementReason;
use App\Pipes\Filters\AppeasementStatus;
use Illuminate\Support\Facades\Pipeline;
use App\Pipes\Filters\BrandRelationByName;
use App\Pipes\Filters\ReasonRelationByName;
use App\Pipes\Filters\AppeasementDateLessThan;
use App\Pipes\Filters\AppeasementDateGreaterThan;
use function Livewire\Volt\{computed, state, updated, uses, usesPagination};

usesPagination();
uses([WithFilters::class]);

state(['search' => ''])->url(as: 'q', except: '');
state(['reason' => ''])->url(except: '');
state(['brand' => ''])->url(except: '');
state(['start' => ''])->url(except: '');
state(['end' => ''])->url(except: '');
state(['status' => ''])->url(except: '');

$brands = computed(fn () => Brand::all());

$reasons = computed(fn () => AppeasementReason::all());

$appeasements = computed(function () {

    return Pipeline::send(Appeasement::query())
        ->through([
            new Search($this->search, ['order_number', 'order_id']),
            new AppeasementStatus($this->status),
            new ReasonRelationByName($this->reason),
            new BrandRelationByName($this->brand),
            new AppeasementDateGreaterThan($this->start),
            new AppeasementDateLessThan($this->end),
            new OrderBy('date', 'desc'),
        ])
        ->thenReturn()
        ->paginate(50, pageName: 'appeasements-page');

});

updated([
    'status' => fn () => $this->resetPage('appeasements-page'),
    'reason' => fn () => $this->resetPage('appeasements-page'),
    'brand' => fn () => $this->resetPage('appeasements-page'),
    'start' => fn () => $this->resetPage('appeasements-page'),
    'end' => fn () => $this->resetPage('appeasements-page'),
    'search' => fn () => $this->resetPage('appeasements-page'),
]);

$filters = computed(function () {
    return [
        'status' => $this->status,
        'reason' => $this->reason,
        'brand' => $this->brand,
        'start' => $this->start,
        'end' => $this->end,
    ];
});

$applyFilters = function () {
    $this->js('$refresh()');
    Flux::modal('filters')->close();
};

$removeFilter = function ($filter) {
    $this->$filter = '';
};

?>

<div class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="xl">Appeasements</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="export-appeasements">
            <flux:button icon="arrow-down-tray" variant="ghost" class="font-extrabold" size="sm">Export</flux:button>
        </flux:modal.trigger>

        <flux:modal.trigger name="bulk-management">
            <flux:button icon="inbox-stack" variant="filled" class="font-extrabold" size="sm">Bulk management</flux:button>
        </flux:modal.trigger>

        <flux:separator vertical/>

        <flux:modal.trigger name="import-appeasements">
            <flux:button icon="arrow-up-tray" variant="primary" size="sm">Bulk import</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->appeasements->total() }} appeasements</flux:text>
    </flux:card>

    <div class="flex gap-4">
        <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by order number, id..." >
            <x-slot name="iconTrailing">
                <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
            </x-slot>
        </flux:input>

        <flux:dropdown>
            
            <flux:button icon="adjustments-horizontal" size="sm" variant="filled">Filters</flux:button>

            <flux:menu>
                <flux:menu.submenu heading="Status">
                    <flux:menu.radio.group wire:model.live="status">
                        @foreach(App\Enums\AppeasementStatus::cases() as $status)
                        <flux:menu.radio value="{{ $status->value }}">{{ ucfirst($status->value) }}</flux:menu.radio>
                        @endforeach
                    </flux:menu.radio.group>
                </flux:menu.submenu>
                <flux:menu.submenu heading="Reason" class="max-h-48">
                    <flux:select wire:model.live="reason" variant="combobox" placeholder="Choose industry...">
                    @foreach ($this->reasons as $reason)  
                       <flux:select.option>{{$reason->name}}</flux:select.option>
                    @endforeach
                    </flux:select>
                </flux:menu.submenu>
                <flux:menu.submenu heading="Brand">
                    <flux:menu.radio.group wire:model.live="brand">
                    @foreach ($this->brands as $brand)  
                       <flux:menu.radio>{{$brand->name}}</flux:menu.radio>
                    @endforeach
                    </flux:menu.radio.group>
                </flux:menu.submenu>
                <flux:menu.submenu heading="Start date">
                    <flux:calendar wire:model.live="start"/>
                </flux:menu.submenu>
                <flux:menu.submenu heading="End date">
                    <flux:calendar wire:model.live="end"/>
                </flux:menu.submenu>
            </flux:menu>
        </flux:dropdown>
        
    </div>

    @if(collect($this->filters)->contains(fn ($value) => !empty($value)))
    <div class="flex items-center gap-2">
        <flux:text>Filters:</flux:text>
        @foreach ($this->filters as $filter => $value)

            @if($value)
            <flux:badge variant="pill" color="amber" size="sm">
                {{ ucfirst($filter) }}: {{ $value }}
                <flux:badge.close wire:click="removeFilter('{{$filter}}')" />
            </flux:badge>
            @endif
            
        @endforeach
    </div>
    @endif

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Order</flux:table.column>
            <flux:table.column class="!px-2">Brand</flux:table.column>
            <flux:table.column class="!px-2">Date of refund</flux:table.column>
            <flux:table.column class="!px-2">Amount</flux:table.column>
            <flux:table.column class="!px-2">Note</flux:table.column>
            <flux:table.column class="!px-2">Reason</flux:table.column>
            <flux:table.column class="!px-2">Status</flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->appeasements as $appeasement)

            <flux:table.row class="hover:bg-zinc-700">
                <flux:table.cell variant="strong" class="!px-2 !py-1.5">{{$appeasement->order_number}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">{{$appeasement->brand?->shorthand}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">{{$appeasement->date}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">${{number_format($appeasement->amount / 100, 2)}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">{{\Illuminate\Support\Str::limit($appeasement->note, 15)}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">{{$appeasement->reason?->name}}</flux:table.cell>
                <flux:table.cell class="!px-2 !py-1.5">
                    @if ($appeasement->status->value != 'processed')
                    <flux:tooltip content="{{$appeasement->status_message}}">
                        <flux:badge color="{{$appeasement->status->color()}}" size="sm">{{ucfirst($appeasement->status->value)}}</flux:badge>
                    </flux:tooltip>
                    @else
                    <flux:badge color="{{$appeasement->status->color()}}" size="sm">{{ucfirst($appeasement->status->value)}}</flux:badge>
                    @endif

                </flux:table.cell>

                <flux:table.cell class="!px-2 !py-1.5">
                    <flux:modal.trigger name="edit-appeasement">
                        <flux:button
                            x-on:mousedown="$dispatch('openEditAppeasementModal', { id: {{ $appeasement->id }}})"
                            icon="pencil"
                            variant="ghost"
                            size="xs" />
                    </flux:modal.trigger>
                </flux:table.cell>
            </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->appeasements" class="border-none"/>

    <livewire:appeasements.import :brands="$this->brands"/>

    <livewire:appeasements.export :totalAppeasements="$this->appeasements->total()" />

    <livewire:appeasements.edit @saved="$refresh"/>

    <livewire:modals.bulk-management-modal historyType="appeasements" :key="'history-' . rand(1, 1000)" />

</div>
