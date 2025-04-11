<?php

use App\Models\AppeasementReason;
use App\Livewire\Forms\AppeasementReasonForm;

use function Livewire\Volt\{on, form};

form(AppeasementReasonForm::class);

on(['openDeleteReasonModal' => fn ($id) => $this->form->setReason(AppeasementReason::find($id)) ]);

$delete = function () {
    $this->form->reason->delete();
    $this->dispatch('deleted');
}

?>

<flux:modal name="delete-reason" class="min-w-[26rem] space-y-6">
    <div>
        <flux:heading size="lg">Delete reason?</flux:heading>

        <flux:subheading>
            <p>Deleting [{{$form->reason->name ?? ''}}] will affect appeasements.</p>
        </flux:subheading>
    </div>

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger">
            <x-slot name="icon">
                <flux:icon.loading wire:loading wire:target="delete" variant="micro"/>
            </x-slot>
            Delete reason
        </flux:button>
    </div>
</flux:modal>