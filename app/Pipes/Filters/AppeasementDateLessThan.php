<?php

namespace App\Pipes\Filters;

use Illuminate\Database\Eloquent\Builder;

class AppeasementDateLessThan
{
    public function __construct(
        private ?string $date,
    ) {}

    public function __invoke(Builder $query, $next)
    {
        if ($this->date) {
            $query->where('date', '<=', $this->date);
        }

        return $next($query);
    }
}
