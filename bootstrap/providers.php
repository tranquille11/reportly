<?php

use Illuminate\Concurrency\ConcurrencyServiceProvider;

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\VoltServiceProvider::class,
    ConcurrencyServiceProvider::class,
];
