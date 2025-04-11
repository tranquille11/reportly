<?php

use App\Models\Call;

use function Livewire\Volt\{computed, usesPagination};

usesPagination();

$calls = computed(fn () => Call::with(['brand', 'agent'])->latest('start_time')->paginate(50, pageName: 'calls-page'));

?>

<div class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="xl">Calls</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="bulk-management">
            <flux:button icon="inbox-stack" variant="filled" class="font-extrabold" size="sm">Bulk management</flux:button>
        </flux:modal.trigger>

        <flux:dropdown>
            <flux:button icon-trailing="chevron-down" variant="filled" size="sm">Bulk actions</flux:button>

            <flux:menu>
                <flux:modal.trigger name="import-agents">
                    <flux:menu.item icon="user-plus">Import agents</flux:menu.item>
                </flux:modal.trigger>

                <flux:modal.trigger name="import-shifts">
                    <flux:menu.item icon="calendar-days">Import shifts</flux:menu.item>
                </flux:modal.trigger>

                <flux:menu.separator/>

                <flux:menu.item icon="arrow-down-tray" wire:click="export">Export agents</flux:menu.item>

                <flux:menu.separator/>

                <flux:modal.trigger name="bulk-management">
                    <flux:menu.item icon="inbox-stack">Bulk management</flux:menu.item>
                </flux:modal.trigger>

            </flux:menu>
        </flux:dropdown>

        <flux:separator vertical/>

        <flux:modal.trigger name="create-agent">
            <flux:button variant="primary" size="sm"> Create agent</flux:button>
        </flux:modal.trigger>

    </div>

    
    <flux:card class="py-3">
        <flux:text> {{ $this->calls->total() }} calls</flux:text>
    </flux:card>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Type</flux:table.column>
            <flux:table.column class="!px-2">Brand</flux:table.column>
            <flux:table.column class="!px-2">Start time</flux:table.column>
            <flux:table.column class="!px-2">End time</flux:table.column>
            <flux:table.column class="!px-2">Agent</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->calls as $call)
                <flux:table.row class="hover:bg-zinc-700">
                    <flux:table.cell class="!px-2">{{$call->call_type}}</flux:table.cell>
                    <flux:table.cell class="!px-2">{{$call->brand->name}}</flux:table.cell>
                    <flux:table.cell class="!px-2">{{$call->start_time}}</flux:table.cell>
                    <flux:table.cell class="!px-2">{{$call->end_time}}</flux:table.cell>
                    <flux:table.cell variant="strong" class="!px-2">{{$call->agent?->name}}</flux:table.cell>
                </flux:table.row>
            @endforeach
            </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->calls" class="border-none"/>

</div>
