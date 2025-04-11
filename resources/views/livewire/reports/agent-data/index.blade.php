<?php

use App\Models\Agent;

use function Livewire\Volt\{state, updating, computed};

state([
    'start' => now()->subDays(7)->startOfWeek()->format('Y-m-d'),
    'end' => now()->subDays(7)->endOfWeek()->format('Y-m-d'),
    'selectedAgents' => [],

    'agentRoles' => ['representative'],
]);

updating([
    'start' => function () {
        unset($this->data);
    },
    'end' => function () {
        unset($this->data);
    },
    'agentRoles' => function () {
        unset($this->data);
    },
]);

$agents = computed(fn () => Agent::select(['id', 'name'])->whereIn('role', $this->agentRoles)->orderBy('name')->get());

?>

<div>
    <div class="flex items-center gap-2">

        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Agent Data</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <flux:dropdown>
            
            <flux:button icon-trailing="chevron-down" size="sm" variant="filled">Filters</flux:button>

            <flux:menu>
                <flux:menu.submenu heading="Agents">

                <flux:select variant="listbox" indicator="checkbox" multiple searchable wire:model.live="selectedAgents" placeholder="Filter agents..." wire:replace.self>
                    @foreach($this->agents as $agent)
                        <flux:select.option value="{{$agent->id}}">{{$agent->name}}</flux:select.option>
                    @endforeach
                </flux:select>
                </flux:menu.submenu>

                <flux:menu.submenu heading="Roles">
                <flux:select variant="listbox" indicator="checkbox" multiple wire:model.live="agentRoles" placeholder="Choose roles..." wire:replace.self>
                @foreach (App\Enums\AgentRole::cases() as $role)
                    <flux:select.option value="{{$role->value}}">{{ucfirst($role->value)}}</flux:select.option>
                @endforeach
                </flux:select>
                </flux:menu.submenu>

                <flux:menu.submenu heading="Start date">
                    <flux:calendar wire:model.live="start"/>
                </flux:menu.submenu>

                <flux:menu.submenu heading="End date">
                    <flux:calendar wire:model.live="end"/>
                </flux:menu.submenu>
            </flux:menu>
        </flux:dropdown>

        <flux:button wire:click="$dispatch('export-agent-data')" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>       

    <div class="flex items-center mt-1 gap-2 mb-6">
        <flux:badge variant="pill" size="sm" color="amber">Start date: {{$start}}</flux:badge>
        <flux:badge variant="pill" size="sm" color="amber">End date: {{$end}}</flux:badge>
        <flux:badge variant="pill" size="sm" color="amber">Roles: {{ \Illuminate\Support\Arr::join($agentRoles, ', ')  }}</flux:badge>
    </div>

    <livewire:reports.agent-data.data :$agentRoles :$start :$end :$selectedAgents lazy :key="str($this->start)->append($this->end)->append(implode('-', $agentRoles))->append(implode('-', $selectedAgents))->toString()"/>
</div>
