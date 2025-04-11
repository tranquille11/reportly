<?php

use function Livewire\Volt\state;
use App\Jobs\GorgiasReasonsForContactJob;
use App\Models\Report;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;

state([
    'timeframe',
    'data' => Report::with(['files' => fn ($q) => $q->latest()->limit(20)])->where('slug', 'reasons-for-contact')->first()->files
]);

$download = fn ($file) => Storage::download('/reports/' . $file); 

$export = function () {

    GorgiasReasonsForContactJob::dispatch($this->timeframe['start'], $this->timeframe['end']);

    Flux::modals()->close();

    Flux::toast(text: 'Reason for contact job has started. You will be notified when it is complete.', heading: 'Import job', variant: 'success');
   
};

?>

<div class="space-y-4">
    <div class="flex items-center gap-2">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Reasons for contact</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <flux:modal.trigger name="reasons-for-contact">
            <flux:button icon="cloud-arrow-down" variant="primary" size="sm">Retrieve from Gorgias</flux:button>
        </flux:modal.trigger>
    </div>     

    <flux:table>
        <flux:table.columns>
            <flux:table.column>
                File
            </flux:table.column>
            <flux:table.column>
                Processed on
            </flux:table.column>
            <flux:table.column>
                Action
            </flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @foreach ($this->data as $row)
                <flux:table.row>
                    <flux:table.cell variant="strong">
                        {{$row->file}}
                    </flux:table.cell>
                    <flux:table.cell>
                        {{$row->created_at->format('Y-m-d')}}
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:button 
                            wire:click="download('{{$row->file}}')" 
                            wire:target="download('{{$row->file}}')"
                            as="a"
                            class="!bg-inherit !border-none !text-amber-400"
                            inset>
                            Download                            
                        </flux:button>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach
        </flux:table.rows>
    </flux:table>

    <flux:modal name="reasons-for-contact" class="w-2/5 space-y-6">
        <div>
            <flux:heading size="lg">Retrieve reasons for contact</flux:heading>
        </div>

        <div class="space-y-2">
            <flux:date-picker wire:model.live="timeframe" mode="range" presets="lastMonth thisMonth" max-range-90 max="today"/>
        </div>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:modal.close>
                <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button wire:click="export" variant="primary">Start</flux:button>
        </div>
    </flux:modal>

</div>
