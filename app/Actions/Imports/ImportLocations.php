<?php

namespace App\Actions\Imports;

use App\Imports\LocationsImport;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportLocations
{
    public function handle(TemporaryUploadedFile $file)
    {
        (new LocationsImport)->import($file);
    }
}
