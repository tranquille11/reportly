<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AppeasementReason extends Model
{
    use Filterable, HasFactory, SoftDeletes;

    protected $filterFields = [
        'name',
    ];

    protected $guarded = [];

    public function appeasements()
    {
        return $this->hasMany(Appeasement::class, 'reason_id');
    }

    public function scopeReasonsForInventoryControl($query)
    {
        return $query->where('shorthand', 'IR')->orWhere('shorthand', 'UN');
    }

    protected function casts()
    {
        return [
            'has_percentage' => 'boolean',
            'has_location' => 'boolean',
            'has_product' => 'boolean',
            'has_size' => 'boolean',
        ];
    }
}
