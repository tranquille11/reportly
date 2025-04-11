<?php

namespace App\Actions\Exports;

use App\Exports\AppeasementsExport;
use App\Jobs\AppeasementsExportJob;
use Illuminate\Container\Attributes\CurrentUser;

class ExportAppeasements
{
    public function __construct(#[CurrentUser] protected $user) {}

    public function handle(array $filters)
    {
        $filename = 'exports/appeasements-'.$this->user->id.now()->timestamp.'.csv';

        (new AppeasementsExport($filters))->queue($filename)->chain([
            new AppeasementsExportJob($this->user->id, $filename),
        ]);

    }
}
