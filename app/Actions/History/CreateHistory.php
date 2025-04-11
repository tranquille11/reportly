<?php

namespace App\Actions\History;

use App\Enums\ImportHistoryStatus;
use App\Enums\ImportHistoryType;
use App\Models\ImportHistory;
use Illuminate\Container\Attributes\CurrentUser;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class CreateHistory
{
    public function __construct(#[CurrentUser] private $user) {}

    public function handle(ImportHistoryType $type, TemporaryUploadedFile $file): ImportHistory
    {
        return $this->user->history()->create([
            'file' => $file->getClientOriginalName(),
            'type' => $type,
            'status' => ImportHistoryStatus::PROCESSING,
        ]);
    }
}
