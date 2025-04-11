<?php

use App\Actions\Imports\ImportAgents;
use App\Exceptions\ImportHeadingColumnException;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Spatie\LivewireFilepond\WithFilePond;

use function Livewire\Volt\{rules, state, uses};

uses(WithFilePond::class);

state('file');
rules(['file' => 'required|mimes:csv|max:2048']);

$downloadTemplate = fn () => Storage::download('templates/agents-template.csv');

$import = function () {
    $this->validateOnly('file');
        
    try {
        app(ImportAgents::class)->handle($this->file);
    } catch (ImportHeadingColumnException $e) {
        Flux::toast(text: $e->getMessage(), variant: 'danger');
        return;
    } finally {
        Flux::modals()->close();
        $this->resetFilePond('file');
    }

    $this->dispatch('imported');
    Flux::toast(text: 'Agents import has been processed.', variant: 'success');
}

 ?>

<flux:modal name="import-agents" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg">Import Agents</flux:heading>
        <flux:subheading>Upload your own CSV file or download the template</flux:subheading>
    </div>

    <div>
        <flux:heading size="sm">Bulk operation</flux:subheading>
        <flux:subheading>Select the operation for the upload</flux:subheading>
    </div>

    <flux:radio label="Create" checked />

    <div>
        <flux:subheading class="flex justify-between mb-1">
            <p>Max upload size: 2MB</p>
            <button wire:click="downloadTemplate">
                <flux:text>Download template</flux:text>
            </button>
        </flux:subheading>
        <x-filepond::upload wire:model.live="file" />
        <flux:error name="file" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="import" variant="primary">Upload</flux:button>
    </div>

</flux:modal>