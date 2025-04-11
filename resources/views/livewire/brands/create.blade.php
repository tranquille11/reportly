<?php

use App\Livewire\Forms\BrandForm;

use function Livewire\Volt\{form, state};

form(BrandForm::class);

state('tags')->reactive();

$create = function () {
    $this->form->create();
    $this->dispatch('created');
}

?>

<flux:modal name="create-brand" variant="flyout" class="space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create brand</flux:heading>
    </div>

    <flux:input wire:model="form.name" label="Name" badge="Required" placeholder="Steve Madden US"/>
    <flux:input wire:model="form.shorthand" label="Short name" badge="Required" placeholder="SMUS"/>

    <flux:select variant="listbox" indicator="checkbox" multiple wire:model="form.tags" placeholder="Choose tags..." label="Tags" badge="Required">
        @forelse ($tags as $tag)
            <flux:select.option>{{ $tag->name }}</flux:select.option>
        @empty
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
