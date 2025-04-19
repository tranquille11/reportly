<?php

use App\Models\Location;
use App\Livewire\Forms\LocationForm;

use function Livewire\Volt\{on, form};

form(LocationForm::class);

on(['openDeleteLocationModal' => fn ($id) => $this->form->setLocation(Location::find($id)) ]);

$delete = function () {

    $this->form->delete();
    $this->dispatch('deleted');
}

?>

<flux:modal name="delete-location" class="min-w-[26rem] space-y-6">
    <div>
        <flux:heading size="lg">Delete location?</flux:heading>

        <flux:subheading>
            <p>Deleting [{{$form->location->name ?? ''}}] will affect appeasements.</p>
        </flux:subheading>
    </div>

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger">
            Delete location
        </flux:button>
    </div>
</flux:modal>
