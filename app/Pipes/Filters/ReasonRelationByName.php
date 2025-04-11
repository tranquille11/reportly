<?php

namespace App\Pipes\Filters;

use Illuminate\Database\Eloquent\Builder;

class ReasonRelationByName
{
    public function __construct(
        private string $name,
    ) {}

    public function __invoke(Builder $query, $next)
    {

        if ($this->name) {
            $query->whereHas('reason', fn ($q) => $q->where('name', $this->name));
        }

        return $next($query);
    }
}
