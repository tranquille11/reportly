<?php

use Spatie\Tags\Tag;
use App\Enums\TagType;
use App\Livewire\Forms\DispositionForm;
use App\Models\Disposition;

use function Livewire\Volt\{form, on, state};

form(DispositionForm::class);

state(['tags']);

on(['openEditDispositionModal' => fn ($id) => $this->form->setDisposition(Disposition::find($id))]);

$save = function () {

    $this->form->save();
    $this->dispatch('saved');

};


?>

<flux:modal name="edit-disposition" variant="flyout" class="max-w-2xl space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Edit</flux:heading>
    </div>

    <flux:input wire:keydown.enter="save" wire:model="form.name" label="Name" badge="Required" placeholder="Shipping Inquiry..."/>

    <flux:select variant="listbox" indicator="checkbox" multiple searchable wire:model="form.tags" placeholder="Choose tags..." label="Tags" badge="Required" wire:replace.self>
        @forelse ($form->tags as $tag)
            <flux:select.option>{{ $tag }}</flux:select.option>
        @empty
        @endforelse

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

        <flux:button wire:click="save" variant="primary">Save</flux:button>
    </div>
</flux:modal>