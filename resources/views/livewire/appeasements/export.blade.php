<?php

use Flux\Flux;
use App\Actions\Exports\ExportAppeasements;
use Illuminate\Support\Facades\Auth;

use function Livewire\Volt\state;

state(['filters']);
state('totalAppeasements')->reactive();

$export = function () {
    
    app(ExportAppeasements::class)->handle($this->filters);

    Flux::modal('export-appeasements')->close();
    Flux::toast('Export has started and will be emailed to ' . Auth::user()->email, variant: 'success');
}

?>

<flux:modal name="export-appeasements" class="w-2/5 space-y-6">
    
    <flux:heading size="lg">Export</flux:heading>
    
    
    <flux:heading>
    Total appeasements to export: {{ $totalAppeasements }}
    </flux:heading>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="export" variant="primary">Export</flux:button>
    </div>

</flux:modal>