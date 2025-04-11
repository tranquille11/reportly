<?php

namespace App\Actions\Imports;

use App\Imports\AppeasementsImport;
use App\Models\Brand;
use App\Models\ImportHistory;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportAppeasements
{
    public function handle(ImportHistory $history, Brand $brand, TemporaryUploadedFile $file): void
    {
        (new AppeasementsImport($brand, $history))->import($file);
    }
}
