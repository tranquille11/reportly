<?php

namespace App\Actions\Imports;

use App\Imports\TagsImport;
use App\Models\ImportHistory;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportTags
{
    public function handle(ImportHistory $history, TemporaryUploadedFile $file): void
    {
        (new TagsImport($history))->import($file);
    }
}
