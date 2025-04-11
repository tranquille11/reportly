<?php

namespace App\Pipes\Actions;

use Illuminate\Database\Eloquent\Builder;

class GetQueryResults
{
    public function __construct() {}

    public function __invoke(Builder $query, $next)
    {
        $models = $query->get();

        return $next($models);
    }
}
