<?php

use Flux\Flux;
use App\Models\Category;
use App\Livewire\Forms\CollectionForm;
use Illuminate\Database\UniqueConstraintViolationException;

use function Livewire\Volt\{form, state};

form(CollectionForm::class);

state(['category' => '']);
state('categories')->reactive();

$create = function () {
    $this->form->create();
    $this->dispatch('created');
};

$addCategory = function () {
    if (! $this->category) return;

    try {
        Category::create(['name' => $this->category]);
    } catch (UniqueConstraintViolationException) {
        Flux::toast("Category already exists", variant: 'danger');
    }

    $this->dispatch('categoryCreated');
    $this->reset('category');
};

?>

<flux:modal name="create-collection" variant="flyout" class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create</flux:heading>
    </div>

    <flux:input wire:model="form.name" label="Name" badge="Required" placeholder="Women's Shoes..."/>

    <flux:select wire:model="form.categories" variant="listbox" indicator="checkbox" label="Category" badge="Required" multiple searchable placeholder="Choose categories...">
        <x-slot name="search">
            <flux:select.search class="px-4" placeholder="Search..." wire:model="category" wire:keydown.enter="addCategory" />
        </x-slot>
        @foreach ($this->categories as $category)
            <flux:select.option value="{{ $category->id }}">{{$category->name}}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input wire:model="form.minimum_quantity" label="Minimum quantity" badge="Required" />
    <flux:input wire:model="form.threshold" label="Threshold" badge="Required" />

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="create" variant="primary">Create</flux:button>
    </div>
</flux:modal>