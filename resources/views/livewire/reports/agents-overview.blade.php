<?php

use App\Models\Agent;
use App\Enums\AgentRole;
use App\Exports\AgentsOverviewExport;
use App\Pipes\Filters\Role;
use App\Traits\WithSorting;
use App\Pipes\Filters\OrderBy;
use Illuminate\Support\Carbon;
use App\Models\AggregateStatistic;
use App\Pipes\Actions\GetQueryResults;
use Illuminate\Support\Facades\Pipeline;
use App\Pipes\Filters\IncludeStatisticsRelationship;
use Maatwebsite\Excel\Facades\Excel;
use App\Pipes\DataTransformation\IncludeStatisticsInAgentModel;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Arr;

use function Livewire\Volt\{computed, placeholder, state, updated, uses};

uses([WithSorting::class]);

placeholder(view('livewire.placeholders.agent-data'));

state([
    'start' => fn () => Carbon::parse(AggregateStatistic::latest('start_date')->select('start_date')->first()?->start_date)->subMonths(6)->format('Y-m-d') ?? '2024-12-01',
    'end' => fn () => AggregateStatistic::latest('start_date')->select('end_date')->first()?->end_date ?? '2024-12-31',
    'months' => function () {

        return AggregateStatistic::query()
            ->select('start_date')
            ->distinct()
            ->get()
            ->pluck('start_date')
            ->map(fn ($date) => Carbon::parse($date)->format('F Y'))
            ->toArray();
    },

    'currentMonth' => fn () => Carbon::parse($this->end)->format('F Y'),

    'columns' => [
        'name', 'total_calls', 'no_disposition', 'outbound_missed', 'hung_up_under_threshold', 'high_hold_time', 'high_talk_time', 'closed_emails', 'closed_chats', 'rating_email', 'rating_chat', 'onetouch'
    ],

    'showHistory' => function () {
        $keys = $this->agents->pluck('name')->map(fn ($item) => str($item)->camel()->toString());
        $values = collect([]);

        foreach ($keys as $key) {
            $values->push(false);
        }

        return $keys->combine($values)->all();
    },

    'currentHistory' => []
]);

state(['selectedAgents' => []])->url();

updated(['currentMonth' => function ($value) {
    $this->start = Carbon::parse($value)->subMonths(6)->firstOfMonth()->format('Y-m-d');
    $this->end = Carbon::parse($value)->lastOfMonth()->format('Y-m-d');
}]);

$agents = computed(function () {

    return Pipeline::send(Agent::query())
        ->through([
            new IncludeStatisticsRelationship($this->start, $this->end),
            new Role(AgentRole::REPRESENTATIVE),
            new OrderBy('name'),
            new GetQueryResults(),
            new IncludeStatisticsInAgentModel()
        ])
        ->thenReturn()
        ->take(5);
    
});

$agents = computed(function () {
    return Agent::whereRole(AgentRole::REPRESENTATIVE)
        ->join('aggregate_statistics', function (JoinClause $join) {
            $join->on('agents.id', "=",'aggregate_statistics.agent_id')->whereBetween('aggregate_statistics.start_date', [$this->start, $this->end]);
        })
        ->selectRaw('agents.name, agents.id, aggregate_statistics.start_date, aggregate_statistics.statistic, aggregate_statistics.number')
        ->orderBy('aggregate_statistics.start_date', 'desc')
        ->get()
        ->groupBy('name')
        ->sortKeys()
        ->map(function ($statistics) {
            $agent = [];

            foreach ($statistics as $statistic) {
                $agent['name'] = $statistic['name'];
                $agent['id'] = $statistic['id'];
                $stat = str_replace('-', '_', $statistic['statistic']);
                $month = Carbon::parse($statistic['start_date']); 
                $lastStatisticMonth = Carbon::parse($this->end);

                if ($month->isSameMonth($lastStatisticMonth)) {
                    $agent['statistics']['current'][$stat] = $statistic['number']; 
                } else {
                    $agent['statistics']['previous'][$month->format('F Y')][$stat] = $statistic['number'];
                }
                
            }

            if (! isset($agent['statistics']['current'])) {
                foreach ($this->columns as $column) {
                    if ($column !== 'name') {
                        $agent['statistics']['current'][$column] = 0;
                    }
                }
            }

            return $agent;
        });
});

$statistics = computed(function () {
    return $this->agents->pluck('statistics.current');
});

$setCurrentHistory = function ($name) {
    $this->currentHistory = $this->agents->get($name);
};

$download = function () {

    $data = $this->agents->pluck('statistics.current', 'name')
        ->map(function ($stats, $name) {
            
            $stats = Arr::prepend($stats, $name, 'name');

            foreach($this->columns as $column) {
                if (!isset($stats[$column]) && $column != 'name') {
                    $stats[$column] = 0;
                }
            }

            return $stats;
        })
        ->toArray();
    
    return Excel::download(
        new AgentsOverviewExport($data),
        "Agents Overview {$this->currentMonth}.xlsx"
    );

};

?>

<div>
    <div class="flex items-center gap-2">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Agents Overview</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <flux:dropdown>
            
            <flux:button icon-trailing="chevron-down" size="sm" variant="filled">Filters</flux:button>

            <flux:menu>
                <flux:menu.submenu heading="Month">
                    <flux:select variant="listbox" placeholder="Choose month" wire:model.live="currentMonth">
                        @foreach($months as $month)
                            <flux:select.option>{{$month}}</flux:option>
                        @endforeach
                    </flux:select>
                </flux:menu.submenu>
            </flux:menu>
        </flux:dropdown>

        <flux:button wire:click="download" variant="ghost" size="sm" icon="cloud-arrow-down">Download</flux:button>

    </div>       

    <div class="space-y-4">

    <div class="flex items-center mt-1 gap-2">
        <flux:badge variant="pill" size="sm" color="amber">Month: {{$currentMonth}}</flux:badge>
    </div>

    <div class="grid grid-cols-5 gap-4">
        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. calls per agent</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.phone-arrow-down-left class="size-5 text-amber-400"/>
                {{ round($this->statistics->pluck('total_calls')->avg(), 0) }}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. emails closed</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.envelope class="size-5 text-amber-400"/>
                {{ round($this->statistics->pluck('closed_emails')->avg(), 0) }}

            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. chats closed</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.chat-bubble-left-ellipsis class="size-5 text-amber-400"/>
                {{ round($this->statistics->pluck('closed_chats')->avg(), 0) }}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. email rating</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.envelope class="size-5 text-amber-400"/>
                {{ round($this->statistics->pluck('rating_email')->avg(), 2) }}

            </flux:heading>
        </flux:card>

        
        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. chat rating</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.chat-bubble-left-ellipsis class="size-5 text-amber-400"/>
                {{ round($this->statistics->pluck('rating_chat')->avg(), 2) }}
            </flux:heading>
        </flux:card>
    </div>

    <flux:table>
    <flux:table.columns>
                <flux:table.column wire:click="sort('name')" class="group !py-1">
                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'name', '!bg-zinc-700') }}" variant="ghost" tooltip="Agent name">
                        Agent
                        @if ($sortBy === 'name')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('name')"/>
                        </x-slot>
                    </flux:button>
                </flux:table.column>
                
                <flux:table.column wire:click="sort('statistics.current.total_calls')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Total calls">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        TC
                        @if ($sortBy === 'statistics.current.total_calls')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.total_calls')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.no_disposition')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.no_disposition', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls without disposition code">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        ND
                        @if ($sortBy === 'statistics.current.no_disposition')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.no_disposition')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.outbound_missed')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.outbound_missed', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls outbound missed">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        OM
                        @if ($sortBy === 'statistics.current.outbound_missed')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.outbound_missed')"/>
                        </x-slot>
                    </flux:button>
                    
                
                </flux:table.column>
                <flux:table.column wire:click="sort('statistics.current.hung_up_under_threshold')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.hung_up_under_threshold', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls hung up by agent under threshold of 30 seconds">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HU<30
                        @if ($sortBy === 'statistics.current.hung_up_under_threshold')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.hung_up_under_threshold')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.high_hold_time')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.high_hold_time', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls with high hold time over threshold of 10 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HT>10
                        @if ($sortBy === 'statistics.current.high_hold_time')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.high_hold_time')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>
                <flux:table.column wire:click="sort('statistics.current.high_talk_time')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.high_talk_time', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls with high talk time over threshold of 15 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        TT>15
                        @if ($sortBy === 'statistics.current.high_talk_time')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.high_talk_time')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.closed_emails')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.closed_emails', '!bg-zinc-700') }}" variant="ghost" tooltip="Emails closed">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EC
                        @if ($sortBy === 'statistics.current.closed_emails')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.closed_emails')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.closed_chats')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.closed_chats', '!bg-zinc-700') }}" variant="ghost" tooltip="Chats closed">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CC
                        @if ($sortBy === 'statistics.current.closed_chats')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.closed_chats')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.rating_email')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.rating_email', '!bg-zinc-700') }}" variant="ghost" tooltip="Email rating">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EMR
                        @if ($sortBy === 'statistics.current.rating_email')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.rating_email')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.rating_chat')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.rating_chat', '!bg-zinc-700') }}" variant="ghost" tooltip="Chat rating">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CHR
                        @if ($sortBy === 'statistics.current.rating_chat')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.rating_chat')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('statistics.current.onetouch')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'statistics.current.onetouch', '!bg-zinc-700') }}" variant="ghost" tooltip="Onetouch rate">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        OT
                        @if ($sortBy === 'statistics.current.onetouch')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('statistics.current.onetouch')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('shifts_worked')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'shifts_worked', '!bg-zinc-700') }}" variant="ghost" tooltip="Number of shifts worked in the period">
                        <x-slot name="icon">
                            <flux:icon.calendar class="size-4 text-green-400" variant="outline"/>
                        </x-slot>
                        SW
                        @if ($sortBy === 'shifts_worked')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('shifts_worked')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

            </flux:table.columns>
            
                    


        <flux:table.rows >

            @foreach ($this->agents->sortBy($sortBy, descending: $sortDirection === 'desc')->when($selectedAgents, fn ($agents) => $agents->whereIn('name', $selectedAgents)) as $agent)

            @php
                $name = str($agent['name'])->camel()->toString();
            @endphp
                <flux:table.row>
                    <flux:table.cell class="!py-1.5 !text-xs">
                        <flux:modal.trigger name="history">
                            <flux:link variant="ghost" class="text-white cursor-pointer" wire:click="setCurrentHistory('{{ $agent['name'] }}')">{{ $agent['name'] }}</flux:link>
                        </flux:modal.trigger>
                    </flux:table.cell>

                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['total_calls'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['no_disposition'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['outbound_missed'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['hung_up_under_threshold'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['high_hold_time'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['high_talk_time'] }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['closed_emails'] ?? 0 }}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['closed_chats'] ?? 0}}</flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                        <div class="flex items-center gap-1">
                            <div>
                            {{ $agent['statistics']['current']['rating_email'] ?? 0 }}
                            </div>
                            <flux:icon.star variant="micro" class="text-yellow-500"/>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                        <div class="flex items-center gap-1">
                            <div>
                            {{ $agent['statistics']['current']['rating_chat'] ?? 0 }}
                            </div>
                            <flux:icon.star variant="micro" class="text-yellow-500"/>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent['statistics']['current']['onetouch']  ?? '0'}}%</flux:table.cell>

                </flux:table.row>
                @if(isset($agent['statistics']['previous']))
                    @foreach($agent['statistics']['previous'] as $month => $timeframe)
                    <flux:table.row wire:show="showHistory.{{ $name }}" wire:transition class="!bg-zinc-700/30">
                        <flux:table.cell class="!px-7 !py-1.5 !text-xs !text-amber-400" variant="strong">
                            {{ $month }}
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['total_calls'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['no_disposition'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['outbound_missed'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['hung_up_under_threshold'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['high_hold_time'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['high_talk_time'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['closed_emails'] ?? 0 }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['closed_chats'] ?? 0}}</flux:table.cell>
                        
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $timeframe['rating_email'] ?? 0 }}
                                </div>
                                <flux:icon.star variant="micro" class="text-yellow-500"/>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $timeframe['rating_chat'] ?? 0 }}
                                </div>
                                <flux:icon.star variant="micro" class="text-yellow-500"/>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400">{{ $timeframe['onetouch'] ?? '0'}}%</flux:table.cell>

                            <flux:table.cell class="!px-5 !py-1.5 !text-xs !text-amber-400"></flux:table.cell>
                        </flux:table.row>
                    
                    @endforeach
                @else
                    <flux:table.row wire:show="showHistory.{{ $name }}" wire:transition>
                        <flux:table.cell class="!px-7 !py-1.5 !text-xs !text-amber-400 flex gap-1">
                            <flux:icon.magnifying-glass variant="micro" class="text-amber-400 mx-auto"/>
                            <div class="mx-auto">No history available</div>
                        </flux:table.cell>
                    </flux:table.row>
                @endif
            @endforeach
        </flux:table.rows>
    </flux:table>
    
    </div>

    <flux:modal name="history" variant="flyout" class="relative w-11/12 !p-0 space-y-6">
        <flux:heading wire:loading.remove wire:target="setCurrentHistory" size="xl" class="!p-4">{{ $currentHistory['name'] ?? '' }}'s history</flux:heading>

        <flux:table wire:loading.remove wire:target="setCurrentHistory">
        <flux:table.columns>

            <flux:table.column class=" !py-1 !font-bold">
                <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Total calls">
                    <x-slot name="icon">
                        <flux:icon.calendar class="size-3"/>
                    </x-slot>
                    Month
                </flux:button>
            </flux:table.column>

            <flux:table.column class=" !py-1 !font-bold">
                <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Total calls">
                    <x-slot name="icon">
                        <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                    </x-slot>
                    TC
                </flux:button>
            </flux:table.column>
                
                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Calls without disposition code">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        ND
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Calls outbound missed">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        OM
                    </flux:button>
                </flux:table.column>
                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Calls hung up by agent under threshold of 30 seconds">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HU<30
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Calls with high hold time over threshold of 10 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HT>10
                    </flux:button>
                    
                </flux:table.column>
                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Calls with high talk time over threshold of 15 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        TT>15
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Emails closed">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EC
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Chats closed">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CC
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Email rating">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EMR
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Chat rating">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CHR
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Onetouch rate">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        OT
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column class="!py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold" variant="ghost" tooltip="Number of shifts worked in the period">
                        <x-slot name="icon">
                            <flux:icon.calendar class="size-4 text-green-400" variant="outline"/>
                        </x-slot>
                        SW
                    </flux:button>
                    
                </flux:table.column>

            </flux:table.columns>
            

            <flux:table.rows>
            

            @if(isset($currentHistory['statistics']['current']))
            <flux:table.row>
                <flux:table.cell class="!py-1.5 !px-2 !text-xs" variant="strong">
                    <flux:badge size="sm" color="purple">{{ Carbon::parse($end)->format('F Y') }}</flux:badge>
                </flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['total_calls'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['no_disposition'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['outbound_missed'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['hung_up_under_threshold'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['high_hold_time'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['high_talk_time'] }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['closed_emails'] ?? 0 }}</flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['closed_chats'] ?? 0}}</flux:table.cell>
                
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                    <div class="flex items-center gap-1">
                        <div>
                        {{ $currentHistory['statistics']['current']['rating_email'] ?? 0 }}
                        </div>
                        <flux:icon.star variant="micro" class="text-yellow-500"/>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                    <div class="flex items-center gap-1">
                        <div>
                        {{ $currentHistory['statistics']['current']['rating_chat'] ?? 0 }}
                        </div>
                        <flux:icon.star variant="micro" class="text-yellow-500"/>
                    </div>
                </flux:table.cell>
                <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $currentHistory['statistics']['current']['onetouch'] ?? '0'}}%</flux:table.cell>

                <flux:table.cell class="!px-5 !py-1.5 !text-xs"></flux:table.cell>
            </flux:table.row>
            @endif
            @if(isset($currentHistory['statistics']['previous']))
                    @foreach($currentHistory['statistics']['previous'] as $month => $timeframe)
                    <flux:table.row>
                        <flux:table.cell class="!py-1.5 !px-2 !text-xs" variant="strong">
                            <flux:badge size="sm" color="purple">{{ $month }}</flux:badge>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['total_calls'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['no_disposition'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['outbound_missed'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['hung_up_under_threshold'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['high_hold_time'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['high_talk_time'] }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['closed_emails'] ?? 0 }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['closed_chats'] ?? 0}}</flux:table.cell>
                        
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $timeframe['rating_email'] ?? 0 }}
                                </div>
                                <flux:icon.star variant="micro" class="text-yellow-500"/>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $timeframe['rating_chat'] ?? 0 }}
                                </div>
                                <flux:icon.star variant="micro" class="text-yellow-500"/>
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $timeframe['onetouch'] ?? '0'}}%</flux:table.cell>

                            <flux:table.cell class="!px-5 !py-1.5 !text-xs"></flux:table.cell>
                        </flux:table.row>
                    @endforeach
                @endif
            </flux:table.rows>
        </flux:table>
        <flux:icon.loading wire:loading wire:target="setCurrentHistory" class="size-8 absolute top-1/2 left-1/2 text-amber-400"/>

    </flux:modal>

    
</div>
