<?php

use App\Models\Agent;
use App\Enums\AgentRole;
use App\Pipes\Filters\Role;
use App\Exports\AgentsExport;
use App\Pipes\Filters\Search;
use App\Pipes\Filters\OrderBy;

use Illuminate\Support\Facades\Pipeline;
use function Livewire\Volt\{state, computed};

state(['search' => ''])->url(as: 'q', except: '');
state('role')->url(except: null);

$agents = computed(function () {

    return Pipeline::send(Agent::query())
    ->through([
        new Search($this->search, ['name', 'stage_name']),
        new Role(AgentRole::tryFrom($this->role)),
        new OrderBy('name')
    ])
    ->thenReturn()
    ->get();
    
});

$removeRole = function () {
    $this->role = null;
};

$export = fn () => (new AgentsExport)->download('agents-export.csv');

?>

<div class="space-y-6">

    <div class="flex items-center gap-2">
        <flux:heading size="xl">Agents</flux:heading>

        <flux:spacer />

        <flux:dropdown>
            <flux:button icon-trailing="chevron-down" variant="filled" size="sm">Bulk actions</flux:button>

            <flux:menu>
                <flux:modal.trigger name="import-agents">
                    <flux:menu.item icon="user-plus">Import agents</flux:menu.item>
                </flux:modal.trigger>

                <flux:modal.trigger name="import-shifts">
                    <flux:menu.item icon="calendar-days">Import shifts</flux:menu.item>
                </flux:modal.trigger>

                <flux:menu.separator/>

                <flux:menu.item icon="arrow-down-tray" wire:click="export">Export agents</flux:menu.item>

                <flux:menu.separator/>

                <flux:modal.trigger name="bulk-management">
                    <flux:menu.item icon="inbox-stack">Bulk management</flux:menu.item>
                </flux:modal.trigger>

            </flux:menu>
        </flux:dropdown>

        <flux:separator vertical/>

        <flux:modal.trigger name="create-agent">
            <flux:button variant="primary" size="sm"> Create agent</flux:button>
        </flux:modal.trigger>

    </div>

    <flux:card class="py-3">
        <flux:text> {{ $this->agents->count() }} agents</flux:text>
    </flux:card>

        <div class="flex gap-4">
            <flux:input wire:model.live.debounce="search" size="sm" icon="magnifying-glass" placeholder="Search by name, stage name or email..." >
                <x-slot name="iconTrailing">
                    <flux:icon.loading wire:loading wire:target="search" variant="micro" class="text-white"/>
                </x-slot>
            </flux:input>

            <flux:dropdown>
                
                <flux:button icon="adjustments-horizontal" size="sm" variant="filled">Filters</flux:button>

                <flux:menu>
                    <flux:menu.submenu heading="Role">
                        <flux:menu.radio.group wire:model.live="role">
                            @foreach(AgentRole::cases() as $position)
                                <flux:menu.radio value="{{$position->value}}">{{ucfirst($position->value)}}</flux:menu.radio>
                            @endforeach
                        </flux:menu.radio.group>
                    </flux:menu.submenu>
                </flux:menu>
            </flux:dropdown>
            
        </div>


        @if($role)
        <div class="flex gap-2 items-center">
            <flux:text>Filters: </flux:text> 
            <flux:badge color="fuchsia" variant="pill">
                Role: {{ ucfirst($role) }}
                <flux:badge.close wire:click="removeRole" class="cursor-pointer"></flux:badge.close>
            </flux:badge>
        </div>
        @endif

        <flux:table>
            <flux:table.columns>
                <flux:table.column>Agent</flux:table.column>
                <flux:table.column>Stage</flux:table.column>
                <flux:table.column>Role</flux:table.column>
                <flux:table.column>Status</flux:table.column>
                <flux:table.column>Created</flux:table.column>
            </flux:table.columns>
            <flux:table.rows>

                @foreach($this->agents as $agent)
                    <flux:table.row>
                        <flux:table.cell class="flex items-center gap-3">
                            <flux:avatar size="xs" name="{{ $agent->name }}" color="auto"/>
                            <flux:link variant="ghost" class="cursor-pointer text-white" href="{{ route('agents.view', $agent->id) }}" wire:navigate>{{ $agent->name }}</flux:link>
                        </flux:table.cell>
                        <flux:table.cell class="whitespace-nowrap">{{ $agent->stage_name }}</flux:table.cell>
                        <flux:table.cell>{{ ucfirst($agent->role->value) }}</flux:table.cell>
                        <flux:table.cell variant="strong">
                            <flux:badge size="sm" color="green" inset="top bottom">Active</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell>
                            {{ $agent->created_at }}
                        </flux:table.cell>
                    </flux:table.row>

                    @endforeach
            </flux:table.rows>
        </flux:table>

    <livewire:agents.create @created="$refresh"/>

    <livewire:agents.import @imported="$refresh"/>
</div>
