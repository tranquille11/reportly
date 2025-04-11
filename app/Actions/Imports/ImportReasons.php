<?php

namespace App\Actions\Imports;

use App\Imports\AppeasementReasonsImport;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportReasons
{
    public function handle(TemporaryUploadedFile $file)
    {
        (new AppeasementReasonsImport)->import($file);
    }
}
