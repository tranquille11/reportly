<?php

use App\Actions\History\CreateHistory;
use App\Actions\Imports\ImportAppeasements;
use Flux\Flux;
use App\Enums\ImportHistoryType;
use App\Rules\AppeasementFileNameRule;
use Illuminate\Support\Facades\Log;
use Spatie\LivewireFilepond\WithFilePond;

use function Livewire\Volt\{uses, rules, state};

uses(WithFilePond::class);

state(['files' => [] , 'brands']);
rules([
    'files' => ['required', new AppeasementFileNameRule],
    'files.*' => 'mimes:xlsx'
]);

$import = function () {

    $this->validate();

    foreach ($this->files as $file) {
        $name = strtolower($file->getClientOriginalName());

        $brand = $this->brands
            ->filter(fn ($brand) => str_starts_with($name, strtolower($brand->shorthand)))
            ->first();

        $history = app(CreateHistory::class)->handle(ImportHistoryType::APPEASEMENTS, $file);

        app(ImportAppeasements::class)->handle($history, $brand, $file);
    }

    Flux::modal('import-appeasements')->close();

    Flux::toast(text: 'Appeasements import job has started. You will be notified when it is complete.', heading: 'Import job', variant: 'success');
    $this->resetFilePond('files');
}

?>

<flux:modal name="import-appeasements" class="w-2/5 space-y-6">
    <div>
        <flux:heading size="lg">Upload</flux:heading>
        <flux:subheading>Upload your own XLSX file</flux:subheading>
    </div>

    <div>
        <flux:heading class="flex items-center gap-2">
            <flux:icon.exclamation-triangle class="text-red-400 size-4" />
            <p class="font-bold !text-red-400">Note:</p>
        </flux:heading>
        <flux:subheading>Appeasement report name must start with brand short name by convention.</flux:subheading>
        <flux:subheading>Example: "SMUS Oct 1st week.xlsx"</flux:subheading>
    </div>

    <div>
        <flux:subheading class="flex justify-between mb-1">
            <p>Max upload size: 5MB. Multiple files allowed.</p>
        </flux:subheading>
        <x-filepond::upload wire:model.live="files" multiple />
        <flux:error name="files" />
    </div>

    <div class="flex gap-2">
        <flux:spacer />
        <flux:modal.close>
            <flux:button variant="filled">Cancel</flux:button>
        </flux:modal.close>
        <flux:button wire:click="import" variant="primary">Upload</flux:button>
    </div>

</flux:modal>