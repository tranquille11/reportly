<?php

use App\Models\Brand;
use App\Livewire\Forms\BrandForm;

use function Livewire\Volt\{form, on, state};

form(BrandForm::class);

state('tags')->reactive();

on(['openEditBrandModal' => fn ($id) => $this->form->setBrand(Brand::find($id)) ]);

$save = function () {
    $this->form->save();
    $this->dispatch('saved');
}

?>

<flux:modal name="edit-brand" variant="flyout" class="max-w-xl space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Edit</flux:heading>
    </div>

    <flux:input wire:loading.remove wire:target="form" wire:keydown.enter="save" wire:model="form.name" label="Name" badge="Required" placeholder="Steve Madden US"/>
    <flux:input wire:keydown.enter="save" wire:model="form.shorthand" label="Short name" badge="Required" placeholder="SMUS"/>

    <div class="space-y-2">
        <flux:select variant="listbox" indicator="checkbox" multiple wire:model="form.tags" placeholder="Choose tags..." label="Tags" badge="Required" wire:replace.self>
            @forelse ($form->tags as $tag)
                <flux:select.option>{{ $tag }}</flux:select.option>
            @empty
            @endforelse

            @forelse ($tags as $tag)
                <flux:select.option>{{ $tag->name }}</flux:select.option>
            @empty
            @endforelse
        </flux:select>
        <div class="space-y-2">
            @foreach ($form->tags as $tag)
                <flux:badge size="sm" color="purple">{{$tag}}</flux:badge>
            @endforeach
        </div>
    </div>
    
    
    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="save" variant="primary">Save</flux:button>
    </div>
</flux:modal>