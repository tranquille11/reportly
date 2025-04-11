<?php

namespace App\Actions\History;

use App\Models\ImportHistory;
use Illuminate\Contracts\Pagination\Paginator;

class RetrieveHistory
{
    public function handle(?array $types, string $time): Paginator
    {
        if (! in_array($time, ['present', 'past'])) {
            throw new \Exception('Invalid time parameter. [present] and [past] supported.');
        }

        return ImportHistory::when($types, fn ($q) => $q->whereIn('type', $types))
            ->when($time == 'present', fn ($q) => $q->onGoing(), fn ($q) => $q->completed())
            ->latest()
            ->simplePaginate(perPage: 10, pageName: $time == 'present' ? 'active-history-page' : 'past-history-page');
    }
}
