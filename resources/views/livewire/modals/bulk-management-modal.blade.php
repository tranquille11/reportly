<?php

use App\Actions\History\RetrieveHistory;

use function Livewire\Volt\{computed, mount, state, usesPagination};

usesPagination();

state([
    'historyType',
    'historyTypes' => [],
]);

mount(function ($historyType) {
    if (str($historyType)->contains(',')) {
        $this->historyTypes = str($historyType)->explode(',')->map(fn ($type) => trim($type))->all();
    } else {
        $this->historyTypes[] = trim($historyType);            
    }
});

$activeHistory = computed(fn () => app(RetrieveHistory::class)->handle(types: $this->historyTypes, time: 'present'));
$pastHistory = computed(fn () => app(RetrieveHistory::class)->handle(types: $this->historyTypes, time: 'past'));

 ?>

<flux:modal name="bulk-management" variant="flyout" class="!p-0 w-full lg:w-3/5" wire:poll.5s>
    <div class="p-6">
        <flux:heading size="xl">Bulk management</flux:heading>
        <flux:subheading>Track the progress of imports</flux:subheading>
    </div>
    <flux:tab.group>
        <flux:tabs class="px-6">
            <flux:tab name="active">Active</flux:tab>
            <flux:tab name="history">History</flux:tab>
        </flux:tabs>
        <flux:tab.panel name="active" class="!p-0">
                <flux:table :paginate="$this->activeHistory">
                    <flux:table.columns>
                        <flux:table.column class="!px-6 !font-bold">File</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Uploaded at</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Uploaded by</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Status</flux:table.column>
                    </flux:table.columns>
                    
                    <flux:table.rows>
                        @forelse ($this->activeHistory as $entry)
                            <flux:table.row>
                                <flux:table.cell class="!px-6">
                                    {{$entry->file}}
                                </flux:table.cell>
                                <flux:table.cell class="!px-6" variant="strong">{{$entry->created_at?->format("M jS, h:i A")}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$entry->user->name}}</flux:table.cell>
                                <flux:table.cell class="!px-6">
                                    <flux:badge size="sm" variant="pill" icon="loading" color="{{$entry->status->color()}}">{{ucfirst($entry->status->value)}}</flux:badge>
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.row>
                                <flux:table.cell colspan="4" class="w-full">
                                    <div class="flex justify-center my-2">
                                        <div class="grid grid-cols-1 gap-1">
                                            <flux:icon.list-checks class="mx-auto text-emerald-600 size-10" />
                                            <flux:heading class="mx-auto font-bold"> All active processes are complete</flux:heading>
                                        </div>
                                    </div>
                                </flux:table.cell>
                            </flux:table.row>
                        @endforelse
                    </flux:table.rows>

                </flux:table>
        </flux:tab.panel>
        <flux:tab.panel name="history" class="!p-0">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column class="!px-6 !font-bold">File</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Uploaded at</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Uploaded by</flux:table.column>
                        <flux:table.column class="!px-6 !font-bold">Status</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @forelse ($this->pastHistory as $entry)
                            <flux:table.row>
                                <flux:table.cell variant="strong" class="!px-6">{{$entry->file}}</flux:table.cell>
                                <flux:table.cell class="!px-6" >{{$entry->created_at?->format("M jS, h:i A")}}</flux:table.cell>
                                <flux:table.cell class="!px-6">{{$entry->user->name}}</flux:table.cell>
                                <flux:table.cell class="!px-6">
                                    @if ($entry->status->value == 'failed')
                                    <flux:tooltip content="{{ $entry->message }}">
                                        <flux:badge size="sm" variant="pill" color="{{$entry->status->color()}}">{{ucfirst($entry->status->value)}}</flux:badge>
                                    </flux:tooltip>    
                                    @else
                                    <flux:badge size="sm" variant="pill" color="{{$entry->status->color()}}">{{ucfirst($entry->status->value)}}</flux:badge>
                                    @endif
                                    
                                </flux:table.cell>
                            </flux:table.row>
                        @empty
                            <flux:table.cell colspan="4" class="w-full">
                                <div class="flex justify-center my-2">
                                    <div class="grid grid-cols-1 gap-1">
                                        <flux:icon.folder-search class="mx-auto size-10 text-yellow-500"/>
                                        <flux:heading class="mx-auto font-bold"> No history</flux:heading>
                                        <p class="text-sm"> Get started by importing a file</p>
                                    </div>
                                </div>
                            </flux:table.cell>
                        @endforelse
                    </flux:table.rows>
                </flux:table>

                <flux:pagination :paginator="$this->pastHistory" class="border-none px-2" />

        </flux:tab.panel>
    </flux:tab.group>
</flux:modal>