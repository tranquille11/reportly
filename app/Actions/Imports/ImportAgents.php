<?php

namespace App\Actions\Imports;

use App\Enums\ImportHistoryStatus;
use App\Enums\ImportHistoryType;
use App\Imports\AgentsImport;
use App\Models\ImportHistory;
use Illuminate\Container\Attributes\CurrentUser;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class ImportAgents
{
    public function __construct(#[CurrentUser] private $user) {}

    public function handle(TemporaryUploadedFile $file): void
    {
        $history = ImportHistory::create([
            'file' => $file->getClientOriginalName(),
            'type' => ImportHistoryType::AGENTS,
            'status' => ImportHistoryStatus::PROCESSING,
            'user_id' => $this->user->id,
        ]);

        (new AgentsImport($history))->import($file);
    }
}
