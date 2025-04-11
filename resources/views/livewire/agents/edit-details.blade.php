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

<flux:modal name="edit-details" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg" class="flex items-center gap-2">
            Edit details
        </flux:heading>

    </div>

    <div class="grid grid-cols-2 gap-6">
        <flux:input wire:model="form.name"
            label="Name"
            badge="Required - Talkdesk"
            placeholder="Full name from Talkdesk..." 
            wire:keydown.enter="save"
            />
        <flux:input wire:model="form.stage_name"
            label="Stage name"
            badge="Required - Gorgias"
            placeholder="Stage name from Gorgias..." 
            wire:keydown.enter="save"
            />
    </div>
            
            <flux:input wire:model="form.email" label="Email" badge="Required" placeholder="Email address..." wire:keydown.enter="save" />

    <flux:select wire:model="form.role" label="Role" variant="listbox" badge="Required" placeholder="Choose role">
        @foreach (App\Enums\AgentRole::cases() as $role)
        <flux:select.option value="{{$role->value}}">{{ucfirst($role->value)}}</flux:option>
        @endforeach
    </flux:select>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="save" variant="primary">Save</flux:button>
    </div>
</flux:modal>