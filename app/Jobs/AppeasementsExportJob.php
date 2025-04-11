<?php

namespace App\Jobs;

use App\Models\User;
use App\Notifications\AppeasementsExportNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AppeasementsExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(private User $user, private string $filename) {}

    public function handle(): void
    {
        $this->user->notify(new AppeasementsExportNotification($this->filename));
    }
}
