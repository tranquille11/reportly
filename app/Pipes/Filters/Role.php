<?php

namespace App\Pipes\Filters;

use App\Enums\AgentRole;
use Illuminate\Database\Eloquent\Builder;

class Role
{
    public function __construct(
        private ?AgentRole $role,
    ) {}

    public function __invoke(Builder $query, $next)
    {
        if ($this->role) {
            $query->where('role', $this->role->value);
        }

        return $next($query);
    }
}
