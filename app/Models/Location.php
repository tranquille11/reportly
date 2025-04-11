<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use Filterable, HasFactory;

    protected $guarded = [];

    protected $filterFields = [
        'name',
    ];

    public function scopeParents($query)
    {
        $query->whereNull('parent_id');
    }

    public function parent()
    {
        return $this->hasOne(Location::class, 'id', 'parent_id');
    }

    public function appeasements()
    {
        return $this->hasMany(Appeasement::class);
    }
}
