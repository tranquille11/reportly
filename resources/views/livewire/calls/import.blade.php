<?php

use App\Enums\ImportHistoryType;
use App\Actions\Imports\ImportCalls;
use App\Actions\History\CreateHistory;
use Spatie\LivewireFilepond\WithFilePond;
use Flux\Flux;

use function Livewire\Volt\{rules, state, uses};

uses(WithFilePond::class);

state('file');
rules(['file' => 'required|mimes:csv']);

$import = function () {

    $this->validate();

    $history = app(CreateHistory::class)->handle(type: ImportHistoryType::CALLS, file: $this->file);
    app(ImportCalls::class)->handle(history: $history, file: $this->file);
 
    Flux::modal('import-calls')->close();
    Flux::toast(heading: "Calls import started", text: "Check the import progress on the Bulk Management modal.", variant: 'success');

};

 

?>

<flux:modal name="import-calls" class="w-2/5 space-y-6">

    <div>

        <flux:heading size="lg">Upload</flux:heading>

        <flux:subheading>Upload your own CSV file</flux:subheading>

    </div>

 

    <div>

        <flux:subheading class="flex justify-between mb-1">

            <p>Max upload size: 10MB.</p>

        </flux:subheading>

        <x-filepond::upload wire:model.live="file" multiple/>

        <flux:error name="file"/>

    </div>

 

    <div class="flex gap-2">

        <flux:spacer/>

        <flux:modal.close>

            <flux:button variant="filled">Cancel</flux:button>

        </flux:modal.close>

        <flux:button wire:click="import" variant="primary">Upload</flux:button>

    </div>

</flux:modal>