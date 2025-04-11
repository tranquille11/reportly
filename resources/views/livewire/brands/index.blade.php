<?php

use App\Enums\TagType;
use App\Models\Brand;
use Spatie\Tags\Tag;

use function Livewire\Volt\{computed, state};

state(['search' => ''])->url(as: 'q', except: '');

$brands = computed(fn () => Brand::with('tags')->search(['name', 'shorthand'], $this->search)->get());

$tagsNotInUse = computed(function () {
    return Tag::getWithType(TagType::TALKDESK->value)->reject(function ($tag, $key) {
        return $this->brands->pluck('tags')->flatten()->where('name', $tag->name)->isNotEmpty();
    });
});

?>

<div class="space-y-6">

    <div class="flex items-center gap-2">
        <flux:heading size="xl">Brands</flux:heading>

        <flux:spacer />
        
        <flux:modal.trigger name="create-brand">
            <flux:button variant="primary" size="sm"> Create brand</flux:button>
        </flux:modal.trigger>

    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->brands->count() }} brands</flux:text>
    </flux:card>


    <div class="flex gap-4">
        <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by name..." >
            <x-slot name="iconTrailing">
                <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
            </x-slot>
        </flux:input>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>Brand</flux:table.column>
            <flux:table.column>Tags</flux:table.column>
            <flux:table.column>Status</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>

            @foreach($this->brands as $brand)
                <flux:table.row>
                    <flux:table.cell class="flex items-center gap-3">
                        <flux:avatar size="xs" name="{{ $brand->name }}" color="auto"/>
                        <div>
                            <flux:text variant="strong" class="cursor-pointer">{{ $brand->name }}</flux:text>
                            <flux:text variant="ghost" class="cursor-pointer">{{ $brand->shorthand }}</flux:text>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="whitespace-nowrap space-x-1">
                        @foreach ($brand->tags as $tag)
                            <flux:badge color="purple" size="sm">{{$tag->name}}</flux:badge>
                        @endforeach
                    </flux:table.cell>
                        <flux:table.cell variant="strong">
                            <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                        </flux:table.cell>

                        <flux:table.cell class="flex justify-end mr-2">
                        <flux:modal.trigger name="edit-brand">
                            <flux:button 
                                variant="ghost" 
                                size="xs" 
                                inset="top bottom" 
                                @mousedown="$dispatch('openEditBrandModal', { id: {{$brand->id}} })">
                                <x-slot name="icon">
                                    <flux:icon.pencil class="size-3"/>
                                </x-slot>
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    </flux:table.row>

                    
                @endforeach
        </flux:table.rows>
    </flux:table>

    <livewire:brands.create @created="$refresh" :tags="$this->tagsNotInUse" />

    <livewire:brands.edit @saved="$refresh" :tags="$this->tagsNotInUse" />
</div>