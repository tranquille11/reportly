<?php
use Flux\Flux;
use App\Models\Agent;
use App\Traits\WithFilters;
use App\Traits\WithSorting;
use Illuminate\Support\Carbon;
use App\Exports\AgentDataExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Actions\Reports\AgentData\DataPerAgent;
use Illuminate\Support\Facades\Concurrency;

use function Livewire\Volt\{computed, mount, on, placeholder, state, updating, uses};

uses([WithSorting::class, WithFilters::class]);

placeholder(view('livewire.placeholders.agent-data'));


state([
    'start',
    'end',
    'selectedAgents',
    'agentRoles',
    'currentAgent' => null,
]);

state(['type' => '']);



$data = computed(fn () => app(DataPerAgent::class)->handle($this->selectedAgents, $this->start, $this->end, $this->agentRoles));

$setCurrentAgent = function ($id, $type) {

    $this->currentAgent = Agent::find($id);
    $this->currentAgent->load([$type => fn($q) => $q->whereBetween('start_time', [Carbon::parse($this->start)->startOfDay(), Carbon::parse($this->end)->endofDay()])]);
    $this->type = $type;

};

on(['export-agent-data' => fn () => $this->export()]);

$export = function () {

    if ($this->data->sum('inbound_calls_count') == 0) {
        Flux::toast(text: 'No data to export.', variant: 'danger');
        return;
    }

    return Excel::download(
        new AgentDataExport(start: Carbon::parse($this->start)->startOfDay(), end: Carbon::parse($this->end)->endOfDay(), roles: $this->agentRoles),
        'Agent_Data ' . Carbon::parse($this->start)->format('m.d') . "-" . Carbon::parse($this->end)->format('m.d') . ".xlsx");

};


?>

<div class="space-y-6">

<div class="grid grid-cols-4 gap-4 py-3">
        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Total agents</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.users class="size-4 text-purple-400"/>
                {{$this->data->count()}}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Total calls</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.phone-arrow-down-left class="size-4 text-purple-400"/>
                {{round($this->data->sum('inbound_calls_count'))}}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Total calls without disposition</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.phone-x-mark class="size-4 text-purple-400"/>
                {{round($this->data->sum('calls_without_disposition_count'))}}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Average calls per agent</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.phone-arrow-down-left class="size-4 text-purple-400"/>
                {{round($this->data->average('inbound_calls_count'))}}
            </flux:heading>
        </flux:card>
    </div>
        <flux:table>
            <flux:table.columns>
                <flux:table.column sortable :sorted="$sortBy === 'name'" :direction="$sortDirection" wire:click="sort('name')">
                    Agent
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('name')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'inbound_calls_count'" :direction="$sortDirection" wire:click="sort('inbound_calls_count')">
                    Total calls
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('inbound_calls_count')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'calls_without_disposition_count'" :direction="$sortDirection" wire:click="sort('calls_without_disposition_count')">
                    No disposition
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('calls_without_disposition_count')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'calls_hung_up_under30_seconds_count'" :direction="$sortDirection" wire:click="sort('calls_hung_up_under30_seconds_count')">
                    Hung up in 30 sec
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('calls_hung_up_under30_seconds_count')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'calls_outbound_missed_count'" :direction="$sortDirection" wire:click="sort('calls_outbound_missed_count')">
                    Outbound missed
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('calls_outbound_missed_count')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'calls_with_high_hold_time_count'" :direction="$sortDirection" wire:click="sort('calls_with_high_hold_time_count')">
                    High hold time
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('calls_with_high_hold_time_count')"/>
                </flux:table.column>
                <flux:table.column sortable :sorted="$sortBy === 'calls_with_high_talk_time_count'" :direction="$sortDirection" wire:click="sort('calls_with_high_talk_time_count')">
                    High talk time
                    <flux:icon.loading class="ml-2 size-3" wire:loading wire:target="sort('calls_with_high_talk_time_count')"/>
                </flux:table.column>
            </flux:table.columns>
            <flux:table.rows>

                @foreach($this->data->sortBy($sortBy, descending: $sortDirection === 'desc')->when($selectedAgents, fn ($c) => $c->whereIn('id', $selectedAgents)) as $agent)
                <flux:table.row>

                <flux:table.cell variant="strong">{{$agent->name}}</flux:table.cell>
                    <flux:table.cell>{{$agent->inbound_calls_count}}</flux:table.cell>
                    <flux:table.cell>{{$agent->calls_without_disposition_count}}</flux:table.cell>
                    <flux:table.cell x-on:mouseenter="$wire.setCurrentAgent({{$agent->id}}, 'callsHungUpUnder30Seconds')">
                        <flux:modal.trigger name="calls">
                            <flux:button variant="subtle" size="sm" class="cursor-pointer !text-purple-300" inset>
                                {{$agent->calls_hung_up_under30_seconds_count}}
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    <flux:table.cell x-on:mouseenter="$wire.setCurrentAgent({{$agent->id}}, 'callsOutboundMissed')">
                        <flux:modal.trigger name="calls">
                            <flux:button variant="subtle" inset size="sm" class="cursor-pointer !text-purple-300">
                                {{$agent->calls_outbound_missed_count}}
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    <flux:table.cell x-on:mouseenter="$wire.setCurrentAgent({{$agent->id}}, 'callsWithHighHoldTime')">
                        <flux:modal.trigger name="calls">
                            <flux:button variant="subtle" inset size="sm" class="cursor-pointer !text-purple-300">
                                {{$agent->calls_with_high_hold_time_count}}
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    <flux:table.cell x-on:mousedown="$wire.setCurrentAgent({{$agent->id}}, 'callsWithHighTalkTime')">
                        <flux:modal.trigger name="calls">
                            <flux:button variant="subtle" inset size="sm" class="cursor-pointer !text-purple-300">
                                {{$agent->calls_with_high_talk_time_count}}
                            </flux:button>
                        </flux:modal.trigger>
                    </flux:table.cell>
                    <flux:table.cell></flux:table.cell>

                </flux:table.row>
                @endforeach
            </flux:table.rows>
        </flux:table>

        <flux:modal  name="calls" variant="flyout" class="relative !p-0 w-11/12 space-y-6">


            <flux:heading wire:loading.remove wire:target="setCurrentAgent" size="xl" class="!p-4">{{$currentAgent?->name}}'s {{Str::headline($type)}}</flux:heading>

            <flux:table wire:loading.remove wire:target="setCurrentAgent">
                <flux:table.columns>
                    <flux:table.column class="!px-6">Agent</flux:table.column>
                    <flux:table.column class="!px-6">Type</flux:table.column>
                    <flux:table.column class="!px-6">Phone</flux:table.column>
                    <flux:table.column class="!px-6">Brand</flux:table.column>
                    <flux:table.column class="!px-6">Start time</flux:table.column>
                    <flux:table.column class="!px-6">End time</flux:table.column>
                    <flux:table.column class="!px-6">Recording</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @if ($type)

                        @php
                            $calls = $currentAgent->$type;
                            $calls->load('brand');
                        @endphp

                        @forelse($calls as $call)
                            <flux:table.row>
                                <flux:table.cell class="!px-6" variant="strong">{{$call->agent->name}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$call->call_type}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$call->phone_number}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$call->brand->name}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$call->start_time}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$call->end_time}}</flux:table.cell>
                                <flux:table.cell class="!px-6">
                                    <flux:link href="{{ $call->recording }}" class="text-purple-400" variant="ghost" target="_blank">Link</flux:link>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                        @endforelse
                    @endif
                </flux:table.rows>
            </flux:table>
            <flux:icon.loading wire:loading wire:target="setCurrentAgent" class="size-8 absolute top-1/2 left-1/2 text-purple-400"/>

        </flux:modal>

</div>
