<?php

use App\Models\AppeasementReason;

use function Livewire\Volt\{computed, state};

state(['search' => ''])->url(as: 'q', except: '');

$reasons = computed(fn () => AppeasementReason::search(['name', 'shorthand'], $this->search)->orderBy('name')->get());

?>

<div class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="xl">Reasons</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="import-reasons">
            <flux:button icon="arrow-up-tray" variant="filled" class="!font-extrabold" size="sm">Bulk import</flux:button>
        </flux:modal.trigger>

        <flux:modal.trigger name="create-reason">
            <flux:button variant="primary" size="sm">Create reason</flux:button>
        </flux:modal.trigger>

    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->reasons->count() }} reasons</flux:text>
    </flux:card>

    <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by name..." >
        <x-slot name="iconTrailing">
            <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
        </x-slot>
    </flux:input>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Name</flux:table.column>
            <flux:table.column class="!px-2">Short</flux:table.column>
            <flux:table.column class="!px-2">Percentage?</flux:table.column>
            <flux:table.column class="!px-2">Location?</flux:table.column>
            <flux:table.column class="!px-2">Item?</flux:table.column>
            <flux:table.column class="!px-2">Size?</flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
        </flux:table.columns>
        
        <flux:table.rows>
            @foreach ($this->reasons as $reason)
                <flux:table.row class="hover:bg-zinc-700">
                    <flux:table.cell variant="strong" class="!px-2">{{$reason->name}}</flux:table.cell>
                    <flux:table.cell class="!px-2">{{$reason->shorthand}}</flux:table.cell>
                    <flux:table.cell class="!px-2">
                        @if($reason->has_percentage)
                            <flux:icon.check-circle class="text-green-400"/>
                        @else
                            <flux:icon.x-circle class="text-red-400"/>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="!px-2">
                        @if($reason->has_location)
                            <flux:icon.check-circle class="text-green-400"/>
                        @else
                            <flux:icon.x-circle class="text-red-400"/>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="!px-2">
                        @if($reason->has_product)
                            <flux:icon.check-circle class="text-green-400"/>
                        @else
                            <flux:icon.x-circle class="text-red-400"/>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="!px-2">
                        @if($reason->has_size)
                            <flux:icon.check-circle class="text-green-400"/>
                        @else
                            <flux:icon.x-circle class="text-red-400"/>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="!px-2 flex justify-end mt-1">
                        <flux:modal.trigger name="edit-reason">
                            <flux:button 
                                variant="ghost" 
                                size="xs" 
                                inset="top bottom" 
                                @mousedown="$dispatch('openEditReasonModal', { id: {{$reason->id}} })">
                                <x-slot name="icon">
                                    <flux:icon.pencil class="size-4" variant="solid"/>
                                </x-slot>
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <livewire:reasons.create @created="$refresh" />

    <livewire:reasons.edit @saved="$refresh" />
    
    <livewire:reasons.delete @deleted="$refresh" />

    <livewire:reasons.import @imported="$refresh" />
</div>
