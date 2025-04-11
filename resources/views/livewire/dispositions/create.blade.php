<?php

use App\Enums\TagType;
use App\Livewire\Forms\DispositionForm;
use Spatie\Tags\Tag;

use function Livewire\Volt\{form, state};

form(DispositionForm::class);

state(['reason' => '', 'tags']);

$create = function () {

    $this->form->create();
    $this->dispatch('created');

};

$addReason = function () {
    if (! $this->reason) {
        return;
    } 
        
    Tag::findOrCreateFromString($this->reason, TagType::GORGIAS_REASON->value);
};

?>

<flux:modal name="create-disposition" class="w-2/5 space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create</flux:heading>
    </div>

    <flux:input wire:model="form.name" label="Name" badge="Required" placeholder="Shipping Inquiry..."/>

    <flux:select variant="listbox" indicator="checkbox" multiple searchable wire:model="form.tags" placeholder="Choose tags..." label="Tags" badge="Required"> 
        <x-slot name="search">
            <flux:select.search class="px-4" placeholder="Search..." wire:model="reason" wire:keydown.enter="addReason" />
        </x-slot>
        @forelse ($tags as $tag)
            <flux:select.option>{{ $tag->name }}</flux:select.option>
            @empty
            <span class="text-sm">No tags available.</span>
        @endforelse
    </flux:select>

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="create" variant="primary">Create</flux:button>
    </div>
</flux:modal>