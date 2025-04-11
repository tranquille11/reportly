<?php

namespace App\Actions\Imports;

use App\Imports\CallsImport;
use App\Models\ImportHistory;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportCalls
{
    public function handle(ImportHistory $history, TemporaryUploadedFile $file): void
    {
        (new CallsImport($history))->import($file);
    }
}
