<?php

namespace App\Pipes\Filters;

use Illuminate\Database\Eloquent\Builder;

class OrderBy
{
    public function __construct(
        private string $column,
        private string $direction = 'asc',
    ) {}

    public function __invoke(Builder $query, $next)
    {
        $query->orderBy($this->column, $this->direction);

        return $next($query);
    }
}
