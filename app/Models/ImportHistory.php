<?php

namespace App\Models;

use App\Enums\ImportHistoryStatus;
use App\Enums\ImportHistoryType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportHistory extends Model
{
    use HasFactory;

    public $guarded = [];

    public function casts()
    {
        return [
            'status' => ImportHistoryStatus::class,
            'type' => ImportHistoryType::class,
            'start_date' => 'datetime',
            'end_date' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCalls($query)
    {
        return $query->where('type', 'calls');
    }

    public function scopeOnGoing($query)
    {
        return $query->where('status', ImportHistoryStatus::PROCESSING);
    }

    public function scopeCompleted($query)
    {
        return $query->whereIn('status', [ImportHistoryStatus::COMPLETED, ImportHistoryStatus::FAILED]);
    }

    public function scopeAppeasements($query)
    {
        return $query->where('type', 'appeasements');
    }
}
