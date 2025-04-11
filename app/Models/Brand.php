<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Tags\HasTags;

class Brand extends Model
{
    use Filterable, HasFactory, HasTags;

    protected $filterFields = [
        'name',
    ];

    protected $guarded = [];

    public function callsInboundOrMissed()
    {
        return $this->hasMany(Call::class, 'brand_id')->inboundOrMissed();
    }

    public function callsOverThreshold()
    {
        return $this->hasMany(Call::class)->inboundOrMissed();
    }

    public function callsUnderThreshold()
    {
        return $this->hasMany(Call::class)->inboundOrMissed();
    }
}
