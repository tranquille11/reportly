<?php

use App\Models\Report;

use function Livewire\Volt\computed;

$reports = computed(fn () => Report::orderBy('name')->get());

?>

<div class="space-y-6">

    <div class="flex items-center gap-2">
        <flux:heading size="xl">Reports</flux:heading>
        
    </div>

    <flux:card class="py-3 shadow-md">
        <flux:text> {{ $this->reports->count() }} reports</flux:text>
    </flux:card>

    
    <flux:table>
        <flux:table.columns>
            <flux:table.column class="!px-2">Report</flux:table.column>
            <flux:table.column class="!px-2">Category</flux:table.column>
            <flux:table.column class="!px-2">Creator</flux:table.column>
            <flux:table.column class="!px-2"></flux:table.column>
        </flux:table.columns>
        <flux:table.rows>

            @foreach($this->reports as $report)
                <flux:table.row>
                    <flux:table.cell class="flex items-center gap-3 !px-2">
                        <flux:link variant="ghost" class="cursor-pointer text-white" href="{{ route('reports.' . $report->slug) }}" wire:navigate> {{ $report->name }}</flux:link>
                    </flux:table.cell>
                    <flux:table.cell variant="strong" class="!px-2">
                        <flux:badge size="sm" color="purple" inset="top bottom">
                            @if($report->type == 'Mixed')
                                Custom
                            @elseif ($report->type == 'Shopify')
                                Appeasements
                            @elseif ($report->type == 'Gorgias')
                                Tickets
                            @elseif ($report->type == 'Talkdesk')
                                Calls
                            @endif
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="flex items-center gap-3 !px-2">
                        <flux:text>Default</flux:text>
                    </flux:table.cell>
                </flux:table.row>
                @endforeach
        </flux:table.rows>
    </flux:table>

</div>