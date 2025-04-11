<?php

use App\Livewire\Forms\AgentForm;
use App\Models\Agent;
use Flux\Flux;

use function Livewire\Volt\{form, mount, state};

form(AgentForm::class);

state(['agent']);

mount(fn (Agent $agent) => $this->form->setAgent($agent));

$save = function () {
    $this->form->save();

    Flux::modals()->close();
    
    $this->dispatch('saved');
}

?>

<flux:modal name="edit-gorgias-id" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg" class="flex items-center gap-2">
            Edit ID
        </flux:heading>

    </div>

    <flux:input wire:model="form.settings.gorgias_user_id"
            wire:keydown.enter="save"
            />
    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="save" variant="primary">Save</flux:button>
    </div>
</flux:modal>