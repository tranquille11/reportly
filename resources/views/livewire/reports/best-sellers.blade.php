<?php

use App\Actions\Reports\BestSellers\ProcessBestSellerReports;
use App\Exports\BestSellersExport;
use App\Models\Category;
use App\Models\Collection;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;
use Spatie\LivewireFilepond\WithFilePond;

use function Livewire\Volt\{computed, rules, state, uses, usesPagination};

uses([WithFilePond::class]);

state([
    'collections' => Collection::with('categories')->get(),
    'categories' => Category::with('collections')->get(),
    'report' => Report::firstWhere('slug', 'best-sellers'),
    'files' => []
]);

rules([
    'files' => 'required',
    'files.*' => 'mimes:csv',
]);

$download = fn ($file) => Storage::download('/reports/' . $file); 

$data = computed(fn () => $this->report->files()->latest()->limit(20)->get());

$import = function () {
    $this->validate();

    try {
        $data = app(ProcessBestSellerReports::class)->handle($this->files, $this->collections, $this->categories);
    } catch (Exception $e) {
        Flux::toast(heading: 'Import failed', text: $e->getMessage(), variant: 'danger');
        return;
    }

    $reportFile = 'Best Sellers_' . now()->format('m_d_Y') . "_" . now()->timestamp . ".xlsx";
    (new BestSellersExport($data))->store("/reports/{$reportFile}");

    $this->report->files()->create(['file' => $reportFile]);

    Flux::modals()->close();
};

?>

<div class="space-y-4">
    <div class="flex items-center gap-2">
        <flux:breadcrumbs>
            <flux:breadcrumbs.item href="{{ route('reports.index') }}" wire:navigate>Reports</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>Best sellers</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <flux:modal.trigger name="import-best-sellers">
            <flux:button icon="cloud-arrow-up" variant="primary" size="sm">Upload</flux:button>
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

    <flux:modal name="import-best-sellers" class="w-2/5 space-y-6">
        <div>
            <flux:heading size="lg">Upload</flux:heading>
            <flux:subheading>Upload your own CSV files</flux:subheading>
        </div>

        <div>
            <flux:heading class="flex items-center gap-2">
                <flux:icon.exclamation-triangle class="text-red-400 size-4"/>
                <flux:text class="font-bold text-red-400">Note:</flux:text>
            </flux:heading>
            <flux:subheading>You must upload the following files:
                <ul>
                    <li>- 1 Shopify report with all items and quantities</li>
                    <li>- 1 Xporter report for each Best Seller category</li>
                </ul>
            </flux:subheading>

        </div>

        <div>
            <flux:subheading class="flex justify-between mb-1">
                <p>Max upload size: 5MB. Multiple files allowed.</p>
            </flux:subheading>
            <x-filepond::upload wire:model.live="files" multiple/>
            <flux:error name="files"/>
        </div>

        <div class="flex gap-2">
            <flux:spacer/>
            <flux:modal.close>
                <flux:button variant="filled">Cancel</flux:button>
            </flux:modal.close>
            <flux:button wire:click="import" variant="primary">Upload</flux:button>
        </div>

    </flux:modal>

</div>