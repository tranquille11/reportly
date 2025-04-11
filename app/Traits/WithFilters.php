<?php

namespace App\Traits;

use Flux\Flux;

trait WithFilters
{
    public function applyFilters()
    {
        $this->js('$refresh()');
        Flux::modal('filters')->close();
    }
}
