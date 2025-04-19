<?php

use App\Actions\Imports\ImportLocations;
use Spatie\LivewireFilepond\WithFilePond;
use App\Exceptions\ImportHeadingColumnException;
use Illuminate\Support\Facades\Storage;
use Flux\Flux;

use function Livewire\Volt\{uses, rules, state};

uses(WithFilePond::class);

state('file');
rules(['file' => 'required']);

$downloadTemplate = fn () => Storage::download('templates/locations-template.csv');

$import = function () {
    
    $this->validate();

    try {
        app(ImportLocations::class)->handle($this->file);
    } catch (ImportHeadingColumnException $e) {
        Flux::toast(text: $e->getMessage(), variant: 'danger');
        return;
    } finally {
        Flux::modal('import-locations')->close();
        $this->resetFilePond('file');
    }

    $this->dispatch('imported');
    Flux::toast(text: 'Locations import has been processed.', variant: 'success');
}

?>

<flux:modal name="import-locations" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg">Upload</flux:heading>
        <flux:subheading>Upload your own CSV file or download the template</flux:subheading>
    </div>

    <div>
        <flux:subheading class="flex justify-between mb-1">
            <p>Max upload size: 2MB</p>
            <button wire:click="downloadTemplate">
                <p class="font-medium text-talkdesk-mauve">Download template</p>
            </button>
        </flux:subheading>
        <x-filepond::upload wire:model.live="file"/>
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