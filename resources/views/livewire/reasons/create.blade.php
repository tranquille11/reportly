<?php

use App\Livewire\Forms\AppeasementReasonForm;

use function Livewire\Volt\{form, updated};

form(AppeasementReasonForm::class);

updated(['form.has_product' => function () {

    if (!$this->form->has_product) {
        $this->form->has_size = false;
    }

}]);


$create = function () {

    $this->form->create();
    $this->dispatch('created');
} 


?>

<flux:modal name="create-reason" class="w-2/5 space-y-6">
    <div class="flex items-center gap-2">
        <flux:heading size="lg">Create</flux:heading>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <flux:input wire:model="form.name" label="Name" badge="Required" placeholder="Reason name..."/>
        <flux:input wire:model="form.shorthand" label="Short name" badge="Required" placeholder="Short name..."/>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <flux:checkbox wire:model="form.has_percentage" label="Has percentage?"/>
        <flux:checkbox wire:model="form.has_location" label="Has location?"/>
        <flux:checkbox wire:model.live="form.has_product" label="Has product?"/>

        @if ($form->has_product)
            <flux:checkbox wire:model="form.has_size" label="Has size?"/>
        @endif
    </div>

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="create" variant="primary">Create</flux:button>
    </div>
</flux:modal>