<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AggregateStatistic extends Model
{
    protected $guarded = [];

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }
}
