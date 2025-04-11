<?php

use App\Livewire\Forms\AppeasementForm;
use App\Models\Appeasement;

use function Livewire\Volt\{form, on};

form(AppeasementForm::class);

on(['openEditAppeasementModal' => fn ($id) => $this->form->setAppeasement(Appeasement::find($id)) ]);

$save = function () {

    $this->form->update();
    $this->dispatch('saved');

};

?>

<flux:modal name="edit-appeasement" class="w-2/5 space-y-6">

    <div class="flex items-center gap-2">
        <flux:heading size="lg">Edit</flux:heading>
    </div>

    <flux:input wire:model="form.note" wire:keydown.enter="save" label="Note" badge="Required" placeholder="MD-20%-TEST BLACK" />
    <flux:input wire:model="form.order_number" wire:keydown.enter="save" label="Order number" badge="Required" placeholder="SMUS#1234567" />

    <div class="flex gap-2">
        <flux:spacer />

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="save" variant="primary">Save</flux:button>
    </div>
</flux:modal>