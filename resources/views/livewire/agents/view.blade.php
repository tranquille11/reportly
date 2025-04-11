<?php

use App\Models\Agent;
use Carbon\CarbonImmutable;
use Flux\Flux;

use function Livewire\Volt\{state, mount};

state('agent');
state(['lastMonth' => CarbonImmutable::now()->startOfMonth()->subMonth()->subMonth()]);

mount(function (Agent $agent) {
    $this->agent = $agent->load(
        [
            'statistics' => function ($q)  {
                $q->where('start_date', $this->lastMonth->format('Y-m-d'))
                  ->whereIn('statistic', ['total-calls','closed-chats', 'closed-emails', 'rating-chat', 'rating-email'])
                ;
            } 
        ]);
});

$deleteAgent = function () {
    $this->agent->delete();

    Flux::toast("Agent {$this->agent->name} has been deleted.", variant: 'success');

    $this->redirectRoute(
        name: 'agents.index',
        navigate: true
    );
};

?>

<div class="w-4/5 mx-auto space-y-4">
    <div class="flex items-center w-full">
        <div>
            <div class="flex gap-1 items-center">
                <flux:button variant="ghost" size="xs" icon="chevron-left" href="{{ route('agents.index') }}" wire:navigate/>
                <flux:heading size="xl"> {{ $agent->name }} </flux:heading>
            </div>
            <flux:text>Created {{ $agent->created_at->diffForHumans() }} </flux:text>
        </div>

        <flux:spacer />

        <flux:dropdown>
            <flux:button size="sm" icon-trailing="chevron-down">More actions</flux:button>
            <flux:menu>
                <flux:modal.trigger name="delete-agent">
                    <flux:menu.item variant="danger">Delete agent</flux:menu.item>
                </flux:modal.trigger>
            </flux:menu>
        </flux:dropdown>
    </div>

    <flux:card class="grid grid-cols-6 p-4 divide-x-1 gap-4 divide-dotted shadow-md">

        <div class="flex items-center">
            <flux:text variant="strong">{{ $lastMonth->format('F Y') }}</flux:text>
        </div>
        <div>
            <flux:heading>Total calls</flux:heading>
            <flux:text>{{ $agent->statistics->firstWhere('statistic', 'total-calls')['number'] ?? 0 }}</flux:text>
        </div>
        <div>
            <flux:heading>Total emails</flux:heading>
            <flux:text>{{ $agent->statistics->firstWhere('statistic', 'closed-emails')['number'] ?? 0 }}</flux:text>
        </div>
        <div>
            <flux:heading>Total chats</flux:heading>
            <flux:text>{{ $agent->statistics->firstWhere('statistic', 'closed-chats')['number'] ?? 0 }}</flux:text>
        </div>
        <div>
            <flux:heading>Rating email</flux:heading>
            <flux:text class="flex items-center gap-1">
                <flux:text>{{ $agent->statistics->firstWhere('statistic', 'rating-email')['number'] ?? 0 }}</flux:text>
                <flux:icon.star variant="solid" class="size-4 text-yellow-500"/>
            </flux:text>
        </div>

        <div>
            <flux:heading>Rating chat</flux:heading>
            <flux:text class="flex items-center gap-1">
                <flux:text>{{ $agent->statistics->firstWhere('statistic', 'rating-chat')['number'] ?? 0 }}</flux:text>
                <flux:icon.star variant="solid" class="size-4 text-yellow-500"/>
            </flux:text>
        </div>
    </flux:card>

    <div class="grid lg:grid-cols-3 gap-4">
        <div class="order-last lg:order-first lg:col-span-2  space-y-6">
            <flux:card class="h-fit shadow-md">

                <flux:heading size="lg">Last 5 calls</flux:heading>

                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>Brand</flux:table.column>
                        <flux:table.column>Start time</flux:table.column>
                        <flux:table.column>Duration</flux:table.column>
                        <flux:table.column>Disposition</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>

                        @foreach($agent->inboundCalls()->orderByDesc('start_time')->limit(5)->get() as $call)
                        <flux:table.row>
                            <flux:table.cell>{{ $call->brand->name }}</flux:table.cell>
                            <flux:table.cell>{{ $call->start_time }}</flux:table.cell>
                            <flux:table.cell>{{ gmdate("H:i:s", $call->talk_time) }}</flux:table.cell>
                            <flux:table.cell>{{ $call->disposition->name ?? '-' }}</flux:table.cell>
                        </flux:table.row>
                        @endforeach

                    </flux:table.rows>
                </flux:table>

            </flux:card>

            <flux:heading size="lg"> Comments </flux:heading>

            <livewire:agents.comments :$agent />
            <!-- End Timeline -->
        </div>
        <div class="space-y-4">
            <flux:card class="col-span-1 space-y-6 shadow-md">
                
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Agent information</flux:heading>

                    <flux:dropdown>
                        <flux:button icon="ellipsis-vertical" variant="ghost" size="xs" />
                        <flux:menu>
                            <flux:modal.trigger name="edit-details">
                                <flux:menu.item icon="clipboard-document-list">Edit details</flux:menu.item>
                            </flux:modal.trigger>

                            <flux:modal.trigger name="edit-role">
                                <flux:menu.item icon="user-cog">Edit role</flux:menu.item>
                            </flux:modal.trigger>

                        </flux:menu>
                    </flux:dropdown>
                </div>

                <div>
                    <flux:heading>Name</flux:heading>            
                    <flux:text>{{ $agent->name }}</flux:text>
                </div>

                <div>
                    <flux:heading>Stage</flux:heading>            
                    <flux:text>{{ $agent->stage_name }}</flux:text>
                </div>
                
                <div>
                    <flux:heading>Email</flux:heading>            
                    <flux:text>{{ $agent->email }}</flux:text>
                </div>
                
                <div>
                    <flux:heading>Role</flux:heading>            
                    <flux:badge color="{{ $agent->role->color() }}" size="sm">{{ ucfirst($agent->role->value) }}</flux:badge>
                </div>
            </flux:card>

            <flux:card class="space-y-6 shadow-md">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Additional details</flux:heading>
                    <flux:dropdown>
                        <flux:button icon="ellipsis-vertical" variant="ghost" size="xs" />
                        <flux:menu>
                            <flux:modal.trigger name="edit-gorgias-id">
                                <flux:menu.item icon="clipboard-document-list">Edit Gorgias ID</flux:menu.item>
                            </flux:modal.trigger>
                        </flux:menu>
                    </flux:dropdown>
                </div>
                <div>
                    <flux:heading>Gorgias user ID</flux:heading>      
                    <flux:badge size="sm">{{ $agent->settings['gorgias_user_id'] ?? 'N/A' }}</flux:badge>
                </div>
            </flux:card>
        </div>
        
    </div>

    <flux:modal name="delete-agent" class="min-w-[26rem]">
        <div>
            <flux:heading size="lg">Delete Agent?</flux:heading>

            <flux:subheading>
                <p>All information about agent [{{ $agent->name }}] will not be recoverable. This action cannot be undone.</p>
            </flux:subheading>
        </div>

        <div class="flex gap-2">
            <flux:spacer/>

            <flux:modal.close>
                <flux:button variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button wire:click="deleteAgent" variant="danger">
                Delete Agent
            </flux:button>
        </div>
    </flux:modal>

    <livewire:agents.edit-details :$agent @saved="$refresh" />
    <livewire:agents.edit-role :$agent @saved="$refresh" />
    <livewire:agents.edit-gorgias-id :$agent @saved="$refresh" />
</div>