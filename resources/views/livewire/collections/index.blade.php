<?php

use App\Models\Category;
use App\Models\Collection;

use App\Jobs\TalkdeskDataAggregation;
use function Livewire\Volt\{computed, mount, on, state};

state(['search' => ''])->url(as: 'q', except: '');

mount(function () {
    TalkdeskDataAggregation::dispatch('2026-01-01');
});

on(['categoryCreated' => function () {
    unset($this->categories);
}]);

$categories = computed(fn () => Category::orderBy('name')->get());

$collections = computed(fn () => Collection::with('categories')->search(['name'], $this->search)->orderBy('name')->get());

?>

<div>
    <div class="flex items-center w-full mb-6">
        <flux:heading size="xl" class="!font-extrabold"> Collections</flux:heading>

        <flux:spacer/>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:modal.trigger name="create-collection">
                <flux:button variant="primary" size="sm"> Create collection</flux:button>
            </flux:modal.trigger>
        </div>
    </div>

        <flux:table>
            <flux:table.columns>
                <flux:table.column class="!px-2">Name</flux:table.column>
                <flux:table.column class="!px-2">Minimum qty</flux:table.column>
                <flux:table.column class="!px-2">Threshold</flux:table.column>
                <flux:table.column class="!px-2"></flux:table.column>
                <flux:table.column class="!px-2"></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @foreach ($this->collections as $collection)
                    <flux:table.row class="hover:bg-zinc-700">
                        <flux:table.cell class="!px-2" variant="strong">{{$collection->name}}</flux:table.cell>
                        <flux:table.cell class="!px-2">{{$collection->minimum_quantity}}</flux:table.cell>
                        <flux:table.cell class="!px-2">{{$collection->threshold}}</flux:table.cell>
                        <flux:table.cell class="!px-2">
                            <flux:dropdown position="bottom" align="center">
                                <flux:button size="xs">{{$collection->categories->count()}} categories</flux:button>

                                <flux:menu class="w-80">
                                    
                                    <flux:heading class="p-4 text-sm font-semibold">{{$collection->categories->count()}} categories</flux:heading>
                                    
                                    <flux:menu.separator />
                                    <div class="p-4 space-y-2">
                                        @foreach ($collection->categories as $category)
                                            <flux:badge color="blue" size="sm" variant="pill">{{$category->name}}</flux:badge>
                                        @endforeach
                                    </div>
                                    
                                </flux:menu>
                            </flux:dropdown>    
                        </flux:table.cell>

                        <flux:table.cell class="!px-2 flex justify-end mt-1">
                            <flux:modal.trigger name="edit-collection">
                                <flux:button 
                                    class=""
                                    variant="ghost" 
                                    size="xs" 
                                    inset="top bottom" 
                                    @mousedown="$dispatch('openEditCollectionModal', { id: {{$collection->id}} })">
                                    <x-slot name="icon">
                                        <flux:icon.pencil class="size-4" variant="solid"/>
                                    </x-slot>
                                </flux:button>
                            </flux:modal.trigger>

                            <flux:modal.trigger name="delete-collection">
                                <flux:button 
                                    class="!text-red-400"
                                    variant="ghost" 
                                    size="xs" 
                                    inset="top bottom" 
                                    @mousedown="$dispatch('openDeleteCollectionModal', { id: {{$collection->id}} })">
                                    <x-slot name="icon">
                                        <flux:icon.trash class="size-4" variant="solid" />
                                    </x-slot>
                                </flux:button>
                            </flux:modal.trigger>
                        </flux:table.cell>
                        
                    </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>
    
    <livewire:collections.create @created="$refresh" :categories="$this->categories" />
    
    <livewire:collections.edit @saved="$refresh" :categories="$this->categories" />

    <livewire:collections.delete @deleted="$refresh" />

</div>