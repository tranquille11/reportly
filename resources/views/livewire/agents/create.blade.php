<?php

use App\Livewire\Forms\AgentForm;

use function Livewire\Volt\form;

form(AgentForm::class);

$create = function () {
    $this->form->create();
    $this->dispatch('created');
}

?>

<flux:modal name="create-agent" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg" class="flex items-center gap-2">
            Create agent
        </flux:heading>

        <flux:subheading>Define a name, email and role</flux:subheading>
    </div>

    <div class="grid grid-cols-2 gap-6">
        <flux:input wire:model="form.name"
            label="Name"
            badge="Required - Talkdesk"
            placeholder="Full name from Talkdesk..." />
        <flux:input wire:model="form.stage_name"
            label="Stage name"
            badge="Required - Gorgias"
            placeholder="Stage name from Gorgias..." />
    </div>

    <flux:input wire:model="form.email" label="Email" badge="Required" placeholder="Email address..." />

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
        <flux:button wire:click="create" variant="primary">Create</flux:button>
    </div>
</flux:modal>