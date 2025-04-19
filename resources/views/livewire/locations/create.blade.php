<?php

use App\Livewire\Forms\LocationForm;

use function Livewire\Volt\{form, updated, updating};

form(LocationForm::class);

updating([
    'form.number' => function ($value) {
        $this->form->name = ucfirst($this->form->type) . " " . $value;
},
    'form.type' => function ($value) {
        $this->form->name = ucfirst($value) . " " . $this->form->number;
},
]);

$create = function () {

    $this->form->create();
    $this->dispatch('created');

}

?>

<flux:modal name="create-location" class="w-2/5 space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create</flux:heading>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <flux:input wire:model.live="form.name" label="Name" badge="Required - Automated" placeholder="Store 123..." disabled/>
        <flux:input wire:model.live="form.number" label="Number" badge="Required" placeholder="123..."/>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <flux:radio.group wire:model.live="form.type" label="Select location type">
            <flux:radio label="Store" value="store"/>
            <flux:radio label="Warehouse" value="warehouse"/>
        </flux:radio.group>
    </div>

    <flux:select wire:model.live="form.parent_id" variant="listbox" searchable placeholder="Select parent location" label="Is sub-location? (EX: 906 is parent for 926)">
        <x-slot name="search">
            <flux:select.search placeholder="Search..."/>
        </x-slot>

        @forelse(App\Models\Location::parents()->orderBy('number')->get() as $location)
            <flux:select.option value="{{$location->id}}">{{$location->name}}</flux:select.option>
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
