<?php

use App\Models\Call;
use App\Models\Agent;
use App\Models\Appeasement;
use App\Models\AppeasementReason;
use function Livewire\Volt\{computed, placeholder, state};

state([
    'start' => now()->firstOfMonth()->subMonth()->format('Y-m-d'),
    'end' => now()->subMonth()->lastOfMonth()->format('Y-m-d'),
]);

placeholder(view('livewire.placeholders.dashboard'));

$appeasements = computed(function () {
    return Appeasement::selectRaw("strftime('%m', date) as month, COUNT(*) as count")
        ->groupByRaw("strftime('%m', date)")
        ->whereBetween('date', [now()->subMonths(2)->firstOfMonth()->format('Y-m-d'), $this->end])
        ->get()
        ->toArray();

})->persist(86400);

$calls = computed(function () {
    return Call::selectRaw("strftime('%m', start_time) as month, COUNT(*) as count")
        ->groupByRaw("strftime('%m', start_time)")
        ->whereBetween('start_time', [now()->subMonths(2)->firstOfMonth()->format('Y-m-d'), $this->end])
        ->inboundOrMissed()
        ->get()
        ->toArray();

})->persist(86400);

$agents = computed(function () {
    return Agent::with(['statistics' => function ($q) {
        return $q->where('start_date', now()->subMonth()->firstOfMonth()->format('Y-m-d'));
    }])
    ->withAvg('inboundCalls', 'talk_time')
    ->get()
    ->map(function ($agent) {

        $agent->total_calls = (int) $agent->statistics->firstWhere('statistic', 'total-calls')?->number;
        $agent->closed_emails = (int) $agent->statistics->firstWhere('statistic', 'closed-emails')?->number;
        $agent->closed_chats = (int) $agent->statistics->firstWhere('statistic', 'closed-chats')?->number;
        $agent->rating_email = $agent->statistics->firstWhere('statistic', 'rating-email')?->number;
        $agent->rating_chat =  $agent->statistics->firstWhere('statistic', 'rating-chat')?->number;

        return $agent;
    })
    ;
})->persist(86400);

$reasons = computed(function () {
    return AppeasementReason::withCount(['appeasements' => function ($q) {
        return $q->whereBetween('date', [now()->subMonth()->firstOfMonth()->startOfDay()->format('Y-m-d'),now()->subMonth()->endOfmonth()->endOfDay()->format('Y-m-d') ]);
    }])->withSum(['appeasements' => fn ($q) => $q->whereBetween('date', [now()->subMonth()->firstOfMonth()->startOfDay()->format('Y-m-d'),now()->subMonth()->endOfmonth()->endOfDay()->format('Y-m-d') ])], 'amount')
        ->orderByDesc('appeasements_count')
        ->limit(5)
        ->get()
    ;
});

?>

<div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">

    <div class="mb-4">
        <flux:heading size="xl"> Good morning, Vlad Enache</flux:heading>
        <flux:subheading>Here is the breakdown for today.</flux:subheading>
    </div>

    @if($this->appeasements && $this->calls)
    <div class="grid auto-rows-min gap-4 md:grid-cols-3">

    <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <div class="flex justify-between items-center">
            <flux:text>Appeasements</flux:text>
            <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
        </div>

        <flux:heading size="xl" class="mt-2 tabular-nums">{{ $this->appeasements[1]['count'] }}</flux:heading>
        
        <div class="flex gap-2 items-center mt-2">
            @if($this->appeasements[1]['count'] < $this->appeasements[0]['count'])
                <flux:icon.arrow-trending-down class="size-4 text-green-400"/>
                <flux:text class="text-xs text-green-400">
                    {{ number_format((($this->appeasements[1]['count'] / $this->appeasements[0]['count']) * 100) - 100, 2) }}% ({{ $this->appeasements[0]['count'] }}) from {{ now()->subMonths(2)->format('F Y') }}
                </flux:text>
            @else
                <flux:icon.arrow-trending-up class="size-4 text-red-400"/>
                <flux:text class="text-xs text-red-400">+{{ number_format((($this->appeasements[1]['count'] / $this->appeasements[0]['count']) * 100) - 100, 2) }}% ({{ $this->appeasements[0]['count'] }}) from {{ now()->subMonths(2)->format('F Y') }}</flux:text>
            @endif
        </div>

    </flux:card>

    <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

    <div class="flex justify-between items-center">
        <flux:text>Calls</flux:text>
        <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
    </div>

    <flux:heading size="xl" class="mt-2 tabular-nums">{{ $this->calls[1]['count'] }}</flux:heading>
        
    <div class="flex gap-2 items-center mt-2">
        @if($this->calls[1]['count'] < $this->calls[0]['count'])
            <flux:icon.arrow-trending-down class="size-4 text-green-400"/>
            <flux:text class="text-xs text-green-400">
                {{ number_format((($this->calls[1]['count'] / $this->calls[0]['count']) * 100) - 100, 2) }}% ({{ $this->calls[0]['count'] }}) from {{ now()->subMonths(2)->format('F Y') }}
            </flux:text>
        @else
            <flux:icon.arrow-trending-up class="size-4 text-red-400"/>
            <flux:text class="text-xs text-red-400">
                +{{ number_format((($this->calls[1]['count'] / $this->calls[0]['count']) * 100) - 100, 2) }}% ({{ $this->calls[0]['count'] }}) from {{ now()->subMonth(2)->format('F Y') }}
            </flux:text>
        @endif
    </div>

    </flux:card>

    <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <flux:text>Agents</flux:text>

    <flux:heading size="xl" class="mt-2 tabular-nums">{{ $this->agents->count() }}</flux:heading>

    </flux:card>
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-2">

        

        <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <div class="flex justify-between items-center">
            <flux:heading size="lg" class="mb-2">Top 5 agents by calls handled</flux:heading>
            <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
        </div>
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Calls</flux:table.column>
                    <flux:table.column>Avg. talk time</flux:table.column>

                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->agents->sortByDesc('total_calls')->take(5) as $agent)
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ $agent->name }}</flux:table.cell>
                        <flux:table.cell>{{ $agent->total_calls }}</flux:table.cell>
                        <flux:table.cell>{{ gmdate('H:i:s', round($agent->inbound_calls_avg_talk_time)) }}</flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <div class="flex justify-between items-center">
            <flux:heading size="lg" class="mb-2">Top 5 agents by emails closed</flux:heading>
            <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
        </div>
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Emails closed</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>

                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->agents->sortByDesc('closed_emails')->take(5) as $agent)
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ $agent->name }}</flux:table.cell>
                        <flux:table.cell>{{ $agent->closed_emails }}</flux:table.cell>
                        <flux:table.cell class="flex items-center gap-1">
                            {{ number_format($agent->rating_email, 2) }}
                            <flux:icon.star variant="micro" class="text-yellow-500"/>
                        </flux:table.cell>

                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>

    <div class="grid auto-rows-min gap-4 md:grid-cols-2">

        <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <div class="flex justify-between items-center">
            <flux:heading size="lg" class="mb-2">Top 5 agents by chats closed</flux:heading>
            <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
        </div>
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Name</flux:table.column>
                    <flux:table.column>Chats closed</flux:table.column>
                    <flux:table.column>Rating</flux:table.column>

                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->agents->sortByDesc('closed_chats')->take(5) as $agent)
                    <flux:table.row>
                        <flux:table.cell variant="strong">{{ $agent->name }}</flux:table.cell>
                        <flux:table.cell>{{ $agent->closed_chats }}</flux:table.cell>
                        <flux:table.cell class="flex items-center gap-1">
                            {{ number_format($agent->rating_chat, 2) }}
                            <flux:icon.star variant="micro" class="text-yellow-500"/>
                        </flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <flux:card class="overflow-hidden min-w-[12rem] shadow-md">

        <div class="flex justify-between items-center">
            <flux:heading size="lg" class="mb-2">Top 5 appeasement reasons</flux:heading>
            <flux:text class="text-xs">{{ now()->subMonth()->format('F Y') }}</flux:text>
        </div>
            
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>Reason</flux:table.column>
                    <flux:table.column>Count</flux:table.column>
                    <flux:table.column>Amount</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach($this->reasons as $reason)
                    <flux:table.row>
                        <flux:table.cell>{{ $reason->name }}</flux:table.cell>
                        <flux:table.cell variant="strong">{{ $reason->appeasements_count }}</flux:table.cell>
                        <flux:table.cell>${{ number_format($reason->appeasements_sum_amount / 100, 2) }}</flux:table.cell>
                    </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
    @endif
</div>
