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

use function Livewire\Volt\{computed, placeholder, state, updated, uses};

uses([WithSorting::class]);

placeholder(view('livewire.placeholders.agent-data'));

state([
    'start' => fn () => AggregateStatistic::latest('start_date')->select('start_date')->first()?->start_date ?? '2024-12-01',
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

    'currentMonth' => fn () => Carbon::parse($this->start)->format('F Y')
]);

state(['selectedAgents' => []])->url();

updated(['currentMonth' => function ($value) {
    $this->start = Carbon::parse($value)->firstOfMonth()->format('Y-m-d');
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
        ->thenReturn();
    
});

$statistics = computed(function () {
    return $this->agents->pluck('statistics')->flatten();
});

$download = function () {

    $columns = [
        'name', 'total_calls', 'no_disposition', 'outbound_missed', 'hung_up_under_threshold', 'high_hold_time', 'high_talk_time', 'closed_emails', 'closed_chats', 'rating_email', 'rating_chat', 'onetouch'
    ];

    $data = $this->agents->select($columns)
        ->map(function ($agent) use ($columns) {
            foreach ($columns as $column) {
                if (!isset($agent[$column])) {
                    $agent[$column] = 0;
                }
            }
            return $agent;
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
                {{ round($this->statistics->where('statistic', 'total-calls')->avg('number'), 0) }}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. emails closed</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.envelope class="size-5 text-amber-400"/>
                {{ round($this->statistics->where('statistic', 'closed-emails')->avg('number'), 0) }}

            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. chats closed</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.chat-bubble-left-ellipsis class="size-5 text-amber-400"/>
                {{ round($this->statistics->where('statistic', 'closed-chats')->avg('number'), 0) }}
            </flux:heading>
        </flux:card>

        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. email rating</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.envelope class="size-5 text-amber-400"/>
                {{ round($this->statistics->where('statistic', 'rating-email')->avg('number'), 2) }}

            </flux:heading>
        </flux:card>

        
        <flux:card wire:target="start, end, selectedAgents, agentRoles" wire:loading.class="opacity-70" class="!relative !shadow-md">
            <div wire:target="start, end, selectedAgents, agentRoles" wire:loading class="absolute top-1/2 left-1/2">
                <flux:icon.loading/>
            </div>
            <flux:subheading>Avg. chat rating</flux:subheading>
            <flux:heading size="xl" class="flex items-center gap-2 !font-extrabold">
                <flux:icon.chat-bubble-left-ellipsis class="size-5 text-amber-400"/>
                {{ round($this->statistics->where('statistic', 'rating-chat')->avg('number'), 2) }}

            </flux:heading>
        </flux:card>
    </div>

    <flux:table class="!max-h-screen">
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
                
                <flux:table.column wire:click="sort('total_calls')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'total_calls', '!bg-zinc-700') }}" variant="ghost" tooltip="Total calls">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        TC
                        @if ($sortBy === 'total_calls')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('total_calls')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('no_disposition')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'no_disposition', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls without disposition code">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        ND
                        @if ($sortBy === 'no_disposition')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('no_disposition')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('outbound_missed')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'outbound_missed', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls outbound missed">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        OM
                        @if ($sortBy === 'outbound_missed')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('outbound_missed')"/>
                        </x-slot>
                    </flux:button>
                    
                
                </flux:table.column>
                <flux:table.column wire:click="sort('hung_up_under_threshold')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'hung_up_under_threshold', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls hung up by agent under threshold of 30 seconds">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HU<30
                        @if ($sortBy === 'hung_up_under_threshold')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('hung_up_under_threshold')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('high_hold_time')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'high_hold_time', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls with high hold time over threshold of 10 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        HT>10
                        @if ($sortBy === 'high_hold_time')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('high_hold_time')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>
                <flux:table.column wire:click="sort('high_talk_time')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'high_talk_time', '!bg-zinc-700') }}" variant="ghost" tooltip="Calls with high talk time over threshold of 15 minutes">
                        <x-slot name="icon">
                            <flux:icon.phone-arrow-down-left class="size-3 text-purple-400"/>
                        </x-slot>
                        TT>15
                        @if ($sortBy === 'high_talk_time')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('high_talk_time')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('closed_emails')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'closed_emails', '!bg-zinc-700') }}" variant="ghost" tooltip="Emails closed">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EC
                        @if ($sortBy === 'closed_emails')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('closed_emails')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('closed_chats')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'closed_chats', '!bg-zinc-700') }}" variant="ghost" tooltip="Chats closed">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CC
                        @if ($sortBy === 'closed_chats')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('closed_chats')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('rating_email')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'rating_email', '!bg-zinc-700') }}" variant="ghost" tooltip="Email rating">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        EMR
                        @if ($sortBy === 'rating_email')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('rating_email')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('rating_chat')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'rating_chat', '!bg-zinc-700') }}" variant="ghost" tooltip="Chat rating">
                        <x-slot name="icon">
                            <flux:icon.chat-bubble-left-ellipsis class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        CHR
                        @if ($sortBy === 'rating_chat')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('rating_chat')"/>
                        </x-slot>
                    </flux:button>
                    
                </flux:table.column>

                <flux:table.column wire:click="sort('onetouch')" class=" !py-1 !font-bold">

                    <flux:button size="xs" class="!font-extrabold {{ when($sortBy === 'onetouch', '!bg-zinc-700') }}" variant="ghost" tooltip="Onetouch rate">
                        <x-slot name="icon">
                            <flux:icon.envelope class="size-4 text-blue-400" variant="outline"/>
                        </x-slot>
                        OT
                        @if ($sortBy === 'onetouch')
                            <flux:icon.chevron-up class="size-3 {{ when($sortDirection === 'desc', 'rotate-180') }}" />
                        @endif
                        <x-slot name="iconTrailing">
                            <flux:icon.loading class="size-3" wire:loading wire:target="sort('onetouch')"/>
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

            <flux:table.rows class="overflow-auto">
                @foreach($this->agents->sortBy($sortBy, descending: $sortDirection === 'desc')->when($selectedAgents, fn ($agents) => $agents->whereIn('name', $selectedAgents)) as $agent)
                    <flux:table.row class="hover:bg-zinc-700">
                        <flux:table.cell variant="strong" class="!px-2 !py-1.5 !text-xs">{{$agent->name}}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->total_calls }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->no_disposition }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->outbound_missed }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->hung_up_under_threshold }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->high_hold_time }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->high_talk_time }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->closed_emails }}</flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">{{ $agent?->closed_chats }}</flux:table.cell>

                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $agent?->rating_email }}
                                </div>
                                @if (! is_null($agent->rating_email))
                                    <flux:icon.star variant="micro" class="text-yellow-500"/>
                                @endif
                            </div>
                        </flux:table.cell>
                        
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            <div class="flex items-center gap-1">
                                <div>
                                {{ $agent?->rating_chat }}
                                </div>

                                @if (! is_null($agent->rating_chat))
                                    <flux:icon.star variant="micro" class="text-yellow-500"/>
                                @endif
                            </div>
                        </flux:table.cell>
                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            {{ $agent?->onetouch }}@if (! is_null($agent->onetouch))%@endif
                        </flux:table.cell>

                        <flux:table.cell class="!px-5 !py-1.5 !text-xs">
                            {{ $agent?->shifts_worked }}
                        </flux:table.cell>

                    </flux:table.row>

                @endforeach
            </flux:table.rows>
        </flux:table>
    </div>

</div>
