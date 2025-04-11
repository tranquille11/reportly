<?php

use App\Models\Collection;
use App\Livewire\Forms\CollectionForm;

use function Livewire\Volt\{form, on};

form(CollectionForm::class);

on(['openDeleteCollectionModal' => fn ($id) => $this->form->setCollection(Collection::find($id)) ]);

$delete = function () {
    $this->form->delete();
    $this->dispatch('deleted');
};

?>

<flux:modal name="delete-collection" class="min-w-[26rem] space-y-6">
    <div>
        <flux:heading size="lg">Delete {{$form->name}}?</flux:heading>

        <flux:subheading>
            <p>This collection will no longer appear in the Best Sellers report.</p>
        </flux:subheading>
    </div>

    <div class="flex gap-2">
        <flux:spacer/>

        <flux:modal.close>
            <flux:button variant="ghost">Cancel</flux:button>
        </flux:modal.close>

        <flux:button wire:click="delete" variant="danger">
            Delete collection
        </flux:button>
    </div>
</flux:modal>