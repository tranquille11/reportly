<?php

namespace App\Pipes\Filters;

use Illuminate\Database\Eloquent\Builder;

class IncludeStatisticsRelationship
{
    public function __construct(
        private string $startDate,
        private string $endDate,
    ) {}

    public function __invoke(Builder $query, $next)
    {
        $query->with(['statistics' => fn ($q) => $q->where('start_date', $this->startDate)->where('end_date', $this->endDate)]);

        return $next($query);
    }
}
