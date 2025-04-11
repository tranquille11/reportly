<?php

namespace App\Traits;

trait WithSorting
{
    public string $sortBy = 'name';

    public string $sortDirection = 'asc';

    public function sort($column)
    {

        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }
}
