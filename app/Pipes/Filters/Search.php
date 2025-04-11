<?php

namespace App\Pipes\Filters;

use Illuminate\Database\Eloquent\Builder;

class Search
{
    public function __construct(
        private string $search,
        private array $columns,
    ) {}

    public function __invoke(Builder $query, $next)
    {
        $query->search($this->columns, $this->search);

        return $next($query);
    }
}
