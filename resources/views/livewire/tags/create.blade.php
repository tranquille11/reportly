<?php

use App\Livewire\Forms\TagForm;

use function Livewire\Volt\form;

form(TagForm::class);

$create = function () {
    $this->form->create();
    $this->dispatch('created');
}

?>

<flux:modal name="create-tag" class="w-2/5 space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create</flux:heading>
    </div>

    <flux:input wire:model="form.name" label="Name" badge="Required" placeholder="Reason name..."/>

    <flux:select wire:model="form.type" variant="listbox" indicator="checkbox" label="Type" badge="Optional" clear="close">
        @foreach (App\Enums\TagType::cases() as $case)
            <flux:select.option>
                {{ $case->value }}
            </flux:select.option>
        @endforeach
    </flux:select>
    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="create" variant="primary">Create</flux:button>
    </div>
</flux:modal>