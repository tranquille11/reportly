<?php

namespace App\Pipes\Filters;

use App\Enums\AppeasementStatus as Status;
use Illuminate\Database\Eloquent\Builder;

class AppeasementStatus
{
    public function __construct(
        private ?string $status,
    ) {}

    public function __invoke(Builder $query, $next)
    {
        if ($this->status) {
            $query->where('status', Status::tryFrom($this->status));
        }

        return $next($query);
    }
}
