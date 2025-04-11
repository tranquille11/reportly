<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Call extends Model
{
    use HasFactory;

    public $guarded = [];

    public function casts()
    {
        return [
            'start_time' => 'datetime',
            'last_day' => 'datetime',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function agent()
    {
        return $this->belongsTo(Agent::class);
    }

    public function scopeOverHoldTime($query)
    {
        return $query->where('hold_time', '>', 600);
    }

    public function scopeInboundOrMissed($query)
    {
        return $query->where('call_type', 'inbound')->orWhere('call_type', 'missed');
    }

    public function scopeInbound($query)
    {
        return $query->where('call_type', 'inbound');
    }

    public function scopeDisconnectedByAgent($query)
    {
        return $query->where('agent_disconnected', true);
    }

    public function scopeUnder($query, $seconds)
    {
        return $query->where('talk_time', '<=', $seconds);
    }

    public function scopeWithoutDisposition($query)
    {
        return $query->where('disposition_id', null);
    }

    public function scopeCallsHungUpUnder30Seconds($query)
    {
        return $query->under(30)->disconnectedByAgent();
    }

    public function scopeBetweenDates($query, $start, $end)
    {
        return $query->whereBetween('start_time', [Carbon::parse($start)->startOfDay(), Carbon::parse($end)->endOfDay()]);
    }

    public function scopeWaitTimeOver($query, $threshold)
    {
        return $query->where('wait_time', '>', $threshold);
    }

    public function scopeWaitTimeUnder($query, $threshold)
    {
        return $query->where('wait_time', '<=', $threshold);
    }

    public function isWithoutDisposition()
    {
        return is_null($this->disposition_id);
    }
}
