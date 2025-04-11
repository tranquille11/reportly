<?php

use App\Models\Location;

use function Livewire\Volt\{computed, state, updating, usesPagination};

usesPagination();

state(['search' => ''])->url(as: 'q', except: '');

$locations = computed(fn () => Location::with('parent')->search(['name', 'number'], $this->search)->orderBy('number')->paginate(20));

updating([
    'search' => fn () => $this->resetPage()
]);

?>

<div class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="xl">Locations</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="import-locations">
            <flux:button icon="arrow-up-tray" variant="filled" class="!font-extrabold" size="sm">Bulk import</flux:button>
        </flux:modal.trigger>

        <flux:modal.trigger name="create-location">
            <flux:button variant="primary" size="sm">Create location</flux:button>
        </flux:modal.trigger>

    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->locations->total() }} locations</flux:text>
    </flux:card>

    <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by name, store #..." >
        <x-slot name="iconTrailing">
            <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
        </x-slot>
    </flux:input>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Name</flux:table.column>
            <flux:table.column class="!px-2">Type</flux:table.column>
            <flux:table.column class="!px-2">Parent location</flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>
            @foreach ($this->locations as $location)
                <flux:table.row class="hover:bg-zinc-700">
                    <flux:table.cell variant="strong" class="!px-2">{{$location->name}}</flux:table.cell>
                    <flux:table.cell class="!px-2">{{ucfirst($location->type)}}</flux:table.cell>
                    <flux:table.cell class="!px-2">
                        @isset($location->parent->name)
                            <flux:badge size="sm" color="amber">{{$location->parent->name}}</flux:badge>
                        @endisset
                    </flux:table.cell>
                    <flux:table.cell class="!px-2 flex justify-end mt-1">
                        <flux:modal.trigger name="edit-location">
                            <flux:button 
                                variant="ghost" 
                                size="xs" 
                                inset="top bottom" 
                                @mousedown="$dispatch('openEditLocationModal', { id: {{$location->id}} })">
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

    <flux:pagination :paginator="$this->locations" class="border-none"/>

</div>
