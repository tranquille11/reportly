<?php

use App\Models\Disposition;
use App\Enums\TagType;
use Spatie\Tags\Tag;

use function Livewire\Volt\{usesPagination, computed, state};

usesPagination();

state(['search' => ''])->url(as: 'q', except: '');

$dispositions = computed(fn () => Disposition::with('tags')->search(['name'], $this->search)->orderBy('name')->paginate(10));

$tagsNotInUse = computed(function () {
    return Tag::getWithType(TagType::GORGIAS_REASON->value)->reject(function ($tag, $key) {
        return $this->dispositions->pluck('tags')->flatten()->where('name', $tag->name)->isNotEmpty();
    });
});
?>

<div class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="xl">Dispositions</flux:heading>

        <flux:spacer />

        <flux:modal.trigger name="create-disposition">
            <flux:button variant="primary" size="sm">Create disposition</flux:button>
        </flux:modal.trigger>
    </div>
    
    <flux:card class="py-3">
        <flux:text> {{ $this->dispositions->count() }} dispositions</flux:text>
    </flux:card>

    <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by name, stage name or email..." >
        <x-slot name="iconTrailing">
            <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
        </x-slot>
    </flux:input>

    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Name</flux:table.column>
            <flux:table.column class="!px-2">Gorgias tags</flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->dispositions as $disposition)
                <flux:table.row class="hover:bg-zinc-700">
                    <flux:table.cell variant="strong" class="!px-2">{{$disposition->name}}</flux:table.cell>
                    <flux:table.cell class="!px-2">
                        <flux:dropdown position="bottom" align="center">
                            <flux:button size="xs">{{$disposition->tags->count()}} tags</flux:button>

                            <flux:menu class="max-w-[28rem]">
                                
                                <flux:heading class="p-4 text-sm font-semibold">{{$disposition->tags->count()}} tags</flux:heading>
                                
                                <flux:menu.separator />
                                <div class="p-4 space-y-2">
                                    @foreach ($disposition->tags->sortBy(fn ($tag) => strlen($tag->name)) as $tag)
                                        <flux:badge color="blue" size="sm" variant="pill">{{$tag->name}}</flux:badge>
                                    @endforeach
                                </div>
                                
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                    <flux:table.cell class="!px-2">
                        <flux:badge variant="pill" size="sm" color="green">Active</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="!px-2">
                        <flux:modal.trigger name="edit-disposition">
                            <flux:button @mousedown="$dispatch('openEditDispositionModal', { id: {{$disposition->id}} })" icon="pencil" variant="ghost" size="xs" inset="top bottom"/>
                        </flux:modal.trigger>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:pagination :paginator="$this->dispositions" class="border-none"/>

    <livewire:dispositions.create :tags="$this->tagsNotInUse" @created="$refresh" />
    <livewire:dispositions.edit :tags="$this->tagsNotInUse" @saved="$refresh" />
</div>
