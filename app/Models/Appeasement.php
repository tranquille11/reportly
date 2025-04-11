<?php

namespace App\Models;

use Abbasudo\Purity\Traits\Filterable;
use App\Enums\AppeasementStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appeasement extends Model
{
    use Filterable, HasFactory;

    protected $filterFields = [
        'status',
        'reason',
        'brand',
        'location',
        'date',
    ];

    protected $guarded = [];

    public function casts()
    {
        return [
            'status' => AppeasementStatus::class,
            'date' => 'datetime',
            'products' => 'array',
            'last_day' => 'date',

        ];
    }

    public function reason()
    {
        return $this->belongsTo(AppeasementReason::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
